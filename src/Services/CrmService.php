<?php

namespace Drupal\clc_web_services\Services;

use Drupal\taxonomy\Entity\Term;
use Drupal\Component\Serialization\Json;

/**
 * Class CrmService.
 */
class CrmService {
  private $wsUrl = NULL;
  private $wsNs = NULL;
  private $wsUser = NULL;
  private $wsPassword = NULL;
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
    $config = \Drupal::state()->get('config_crm_services', NULL);

    if (!empty($config)) {
      $this->wsUrl = $config['ws_endpoint'];
      $this->wsNs = $config['ws_namespace'];
      $this->wsUser = $config['ws_user'];
      $this->wsPassword = $config['ws_password'];
      $this->debug = $config['ws_debug'];
    }
  }

  /**
   * Function validateConf().
   *
   * Validate data configuration exits.
   */
  private function validateConf() {
    if ($this->wsUrl && $this->wsNs && $this->wsUser && $this->wsPassword) {
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
        $this->data['subject'] = $values['subject'];
        $this->data['document_type'] = $values['document_type'];
        $this->data['document'] = $values['document'];
        $this->data['name'] = $values['name'];
        $this->data['lastname'] = $values['lastname'];
        $this->data['email'] = $values['email'];
        $this->data['cellphone'] = $values['phone'];
        $this->data['status'] = $values['terms'];
        $this->data['comment'] = $values['comment'];

        // Get data about city.
        $this->getCityInformation($values['states'], $values['cities']);
        break;
    }

    // Validations.
    foreach ($this->data as $key => $value) {
      switch ($key) {
        case 'subject':
          // The field SUBJECT is required and validate format.
          $this->validateFieldRequired($value);
          $this->validateFieldNumeric($key, $value);
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
      $subject = isset($this->data['subject']) ? $this->data['subject'] : NULL;
      $docType = isset($this->data['document_type']) ? $this->data['document_type'] : NULL;
      $document = isset($this->data['document']) ? $this->data['document'] : NULL;
      $name = isset($this->data['name']) ? $this->data['name'] : NULL;
      $lastname = isset($this->data['lastname']) ? $this->data['lastname'] : NULL;
      $email = isset($this->data['email']) ? $this->data['email'] : NULL;
      $cellphone = isset($this->data['cellphone']) ? $this->data['cellphone'] : NULL;
      $city = isset($this->data['city']) ? $this->data['city'] : NULL;
      $status = $this->data['status'] ? 1 : 2;
      $comment = isset($this->data['comment']) ? $this->data['comment'] : NULL;

      $params = array(
        'usuario' => $this->wsUser,
        'password' => $this->wsPassword,
        'tipo_documento' => $docType,
        'documento_cliente' => $document,
        'nombre_cliente' => $name,
        'apellido_cliente' => $lastname,
        'email' => $email,
        'telefono' => $cellphone,
        'celular' => $cellphone,
        'ciudad' => $city,
        'tema' => $subject,
        'comentario' => $comment,
        'autorizo' => $status,
        'unidad' => 'akt',
      );

      try {
        // Web service Call.
        $client = new \nusoap_client($this->wsUrl, TRUE);
        $client->soap_defencoding = 'UTF-8';
        $client->namespaces['pqr'] = $this->wsNs;
        $result = $client->call('crea_caso', $params);
      }
      catch (Exception $e) {
        // @Watchdog
      }

      if ($this->debug) {
        // Log parameters sended.
        \Drupal::logger('CRM Services')->notice('Parameters sended: ' . Json::encode($params));
        // Log response.
        \Drupal::logger('CRM Services')->notice('Result: ' . Json::encode($result));
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
