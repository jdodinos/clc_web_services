<?php

namespace Drupal\clc_web_services\Services;

// use Drupal\taxonomy\Entity\Term;
use Drupal\Component\Serialization\Json;
use Drupal\file\Entity\File;

/**
 * Class VozClienteKalley.
 */
class VozClienteKalley {
  private $wsUrl = NULL;
  private $environment = 'env_development';
  private $debug = FALSE;
  private $data = [];
  public $validate = FALSE;
  public $error = FALSE;
  public $message = [];

  /**
   * Constructor.
   */
  public function __construct() {
    $this->getConfiguration();
    $this->validateConf();
  }

  /**
   * Function getConfiguration().
   *
   * Get configuration module.
   */
  private function getConfiguration() {
    $config = \Drupal::state()->get('config_cliente_kalley_services', NULL);

    if (!empty($config)) {
      $this->wsUrl = $config['ws_endpoint'];
      $this->environment = $config['ws_environment'];
      $this->debug = $config['ws_debug'];
    }
  }

  /**
   * Function validateConf().
   *
   * Validate data configuration exits.
   */
  private function validateConf() {
    if ($this->wsUrl) {
      $this->validate = TRUE;
    }
  }

  /**
   * Function validateDataForm().
   *
   * Validate form values.
   */
  public function validateDataForm($form_id, $values) {
    $this->data = [];

    switch ($form_id) {
      case 'webform_submission_contactanos_y_pqrs_add_form':
        $this->data['document_type'] = $values['document_type'];
        $this->data['document'] = $values['document'];
        $this->data['name'] = $values['name'];
        $this->data['lastname'] = $values['lastname'];
        $this->data['email'] = $values['email'];
        $this->data['cellphone'] = $values['phone'];
        $this->data['state'] = $values['states'];
        $this->data['city'] = $values['cities'];
        $this->data['subject_id'] = $values['subject'];
        $this->data['comment'] = $values['comment'];
        $this->data['status'] = $values['terms'];

        // Information about form.
        if ($this->environment == 'env_production') {
          $this->data['source_of_information'] = 2;
          $this->data['pqr_type'] = 12;
          $this->data['class'] = 83;
        }
        else {
          $this->data['source_of_information'] = 2;
          $this->data['pqr_type'] = 11;
          $this->data['class'] = 53;
        }

        if ($values['subject'] == 5) {
          $this->data['source_of_information'] = 10;
          if ($this->environment == 'env_production') {
            $this->data['source_of_information'] = 15;
          }

          $this->data['address'] = $values['address'];
        }
        break;

      case 'webform_submission_recoleccion_add_form':
        $this->data['document_type'] = $values['document_type'];
        $this->data['document'] = $values['document'];
        $this->data['name'] = $values['name'];
        $this->data['lastname'] = $values['lastname'];
        $this->data['address'] = $values['address'];
        $this->data['email'] = $values['email'];
        $this->data['cellphone'] = $values['phone'];
        $this->data['state'] = $values['states'];
        $this->data['city'] = $values['cities'];
        $this->data['subject_id'] = 6;
        $this->data['comment'] = $values['comment'];
        $this->data['status'] = $values['terms'];

        // Information about form.
        if ($this->environment == 'env_production') {
          $this->data['source_of_information'] = 14;
          $this->data['pqr_type'] = 1;
          $this->data['class'] = 18;
        }
        else {
          $this->data['source_of_information'] = 10;
          $this->data['pqr_type'] = 1;
          $this->data['class'] = 15;
        }
        break;
    }

    if (!empty($values['sales_slip'])) {
      $file = File::load($values['sales_slip']);
      if ($file) {
        $fileName = $file->getFilename();
        $fileSize = $file->getSize();
        $fileMimeType = $file->getMimeType();
        $image = file_get_contents($file->getFileUri());
        $base = base64_encode($image);

        $this->data['file'] = [
          'name' => $fileName,
          'type' => $fileMimeType,
          'contenido' => $base,
          'error' => 0,
          'size' => $fileSize,
        ];
      }
    }

    // Validations.
    foreach ($this->data as $key => $value) {
      switch ($key) {
        case 'subject':
          // The field SUBJECT is required and validate format.
          $this->validateFieldRequired($value);
          // $this->validateFieldNumeric($key, $value);
          break;

        case 'document':
          // The field DOCUMENT is required.
          $this->validateFieldRequired($value);
          break;

        case 'name':
          // The field NAME is required.
          $this->validateFieldRequired($value);
          break;

        case 'lastname':
          // The field LASTNAME is required.
          $this->validateFieldRequired($value);
          break;

        case 'email':
          // The field EMAIL is required and validate format.
          $this->validateFieldRequired($value);
          $this->validateFieldEmail($value);
          break;

        case 'cellphone':
          // The field CELLPHONE is required.
          $this->validateFieldRequired($value);
          $this->validateFieldNumeric($key, $value);
          break;

        case 'comment':
          // The field COMMENT is required.
          $this->validateFieldRequired($value);
          break;

        case 'file':
          // The field FILE have limit in 5MB.
          if ($value['size'] > 5242880) {
            $this->validate = FALSE;
            $this->message[$field] = t('The field exceeds the allowed limit');
          }
          break;
      }
    }
  }

  /**
   * Function validateFieldRequired().
   *
   * Validate field required.
   */
  private function validateFieldRequired($value) {
    if ($value == '') {
      $this->validate = FALSE;
    }
  }

  /**
   * Function validateFieldEmail().
   *
   * Validate field format Email.
   */
  private function validateFieldEmail($value) {
    if (!valid_email_address($value)) {
      $this->validate = FALSE;
    }
  }

  /**
   * Function validateFieldNumeric().
   *
   * Validate field format Numeric.
   */
  private function validateFieldNumeric($field, $value) {
    if (!is_numeric($value)) {
      $this->validate = FALSE;

      if (!$this->validate) {
        $name_field = t($field);
        $this->message[$field] = t('The field @f is numeric', ['@f' => $name_field]);
      }
    }
  }

  /**
   * Function sendDataToWs().
   *
   * Send data to WS.
   */
  public function sendDataToWs() {
    if ($this->validate) {
      // The customer data.
      $subject_id = isset($this->data['subject_id']) ? $this->data['subject_id'] : NULL;
      $docType = isset($this->data['document_type']) ? $this->data['document_type'] : NULL;
      $document = isset($this->data['document']) ? $this->data['document'] : NULL;
      $name = isset($this->data['name']) ? $this->data['name'] : NULL;
      $lastname = isset($this->data['lastname']) ? $this->data['lastname'] : NULL;
      $email = isset($this->data['email']) ? $this->data['email'] : NULL;
      $cellphone = isset($this->data['cellphone']) ? $this->data['cellphone'] : NULL;
      $state = isset($this->data['state']) ? $this->data['state'] : NULL;
      $city = isset($this->data['city']) ? $this->data['city'] : NULL;
      $address = isset($this->data['address']) ? $this->data['address'] : NULL;
      $status = $this->data['status'] ? 1 : 2;
      $comment = isset($this->data['comment']) ? $this->data['comment'] : NULL;
      $file = isset($this->data['file']) ? $this->data['file'] : NULL;

      $area = 4;
      $stock = 'AL009';
      if ($this->environment == 'env_production') {
        $area = 1;
        $stock = 'OTR';
      }

      // Information general for web service.
      $clase = $this->data['class'];
      $fuenteDeInformacion = $this->data['source_of_information'];
      $tipoPqr = $this->data['pqr_type'];
      $params = [
        'nombres' => $name,
        'apellidos' => $lastname,
        'tipoIdentificacion' => $docType,
        'numeroDeIdentificacion' => $document,
        'email' => $email,
        'departamento' => $state,
        'direccion' => $address,
        'ciudad' => $city,
        'telefonoCelular' => $cellphone,
        'comentario' => $comment,
        'archivoAdjunto' => $file,
        'area' => $area,
        'almacen' => $stock,
        'clase' => $clase,
        'fuenteDeInformacion' => $fuenteDeInformacion,
        'tipoPqr' => $tipoPqr,
        'asunto' => $subject_id,
      ];

      // Make the request.
      $options = [
        'connect_timeout' => 30,
        'headers' => [
          'Content-Type' => 'text/json',
        ],
        'body' => Json::encode($params),
        'verify' => TRUE,
      ];

      // Log parameters sended.
      if ($this->debug) {
        if(isset($params['archivoAdjunto']['contenido'])) {
          $params['archivoAdjunto']['contenido'] = 'File Base64';
        }
        \Drupal::logger('VozClienteKalley')->notice('Parametros enviados: ' . Json::encode($params));
      }

      try {
        $client = \Drupal::httpClient();
        $request = $client->request('POST', $this->wsUrl, $options);
        $result = $request->getBody()->getContents();
        $result = Json::decode($result);
      }
      catch (RequestException $e) {
        // Log the error.
        // watchdog_exception('custom_modulename', $e);
      }

      $codes_accepted = ['PD001'];
      if (isset($result['codmensaje']) && in_array($result['codmensaje'], $codes_accepted)) {
        // The data has been saved.
        /* Some code here. */
      }
      else {
        // An error has occurred.
        $this->error = TRUE;

        // Send information about the error.
        $params = '<code_response>' . $result['codmensaje'] . '</code_response>' . $params;
        \Drupal::logger('VozClienteKalley')->error('Error ' . Json::encode($params));
      }

      // Log response.
      if ($this->debug) {
        \Drupal::logger('VozClienteKalley')->notice('Result: ' . $result['codmensaje'] . ' ' . $result['descripcionmensaje']);
      }
    }
  }

  /**
   * Function getCityInformation().
   *
   * Get information about cities.
   */
  private function getCityInformation($state_id, $city_id) {
    // Initiallize variables.
    $state_name = $city_name = NULL;

    // Load the state.
    $term_state = Term::load($state_id);
    if ($term_state) {
      // Get name state.
      $state_name = $term_state->get('name')->value;

      // Load the city.
      $term_city = Term::load($city_id);
      if ($term_city) {
        // Get name city.
        $city_name = $term_city->get('name')->value;
      }
    }

    // Join state and city to return.
    $this->data['city'] = "{$city_name}, {$state_name}";
  }

}
