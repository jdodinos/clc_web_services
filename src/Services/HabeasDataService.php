<?php

namespace Drupal\clc_web_services\Services;

use Drupal\Component\Serialization\Json;

/**
 * Class HabeasDataService.
 */
class HabeasDataService {
  private $wsUrl = NULL;
  private $wsNs = NULL;
  private $business = NULL;
  private $nidTyc = NULL;
  private $system = 'Drupal';
  private $canal = 'WEB';
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
    $config = \Drupal::state()->get('config_habeas_data', NULL);

    if (!empty($config)) {
      $this->wsUrl = $config['ws_endpoint'];
      $this->wsNs = $config['ws_namespace'];
      $this->business = $config['business'];
      $this->nidTyc = $config['nid_terms'];
      $this->system = 'Drupal';
      $this->canal = 'WEB';
      $this->debug = $config['ws_debug'];
    }
  }

  /**
   * Function validateConf().
   *
   * Validate data configuration exits.
   */
  private function validateConf() {
    if ($this->wsUrl && $this->wsNs && $this->business && $this->nidTyc) {
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
        $this->data['form_name'] = 'Contactanos y PQRS';
        $this->data['document_type'] = $values['document_type'];
        $this->data['document'] = $values['document'];
        $this->data['name'] = $values['name'];
        $this->data['lastname'] = $values['lastname'];
        $this->data['email'] = $values['email'];
        $this->data['cellphone'] = $values['phone'];
        $this->data['status'] = $values['terms'];
        break;

      case 'webform_submission_contactanos_y_pqrs_add_form':
        $this->data['form_name'] = 'Contactanos y PQRS';
        $this->data['document_type'] = $values['document_type'];
        $this->data['document'] = $values['document'];
        $this->data['name'] = $values['name'];
        $this->data['lastname'] = $values['lastname'];
        $this->data['email'] = $values['email'];
        $this->data['cellphone'] = $values['phone'];
        $this->data['status'] = $values['terms'];
        break;
      case 'webform_submission_recoleccion_add_form':
        $this->data['form_name'] = 'Recoleccion';
        $this->data['document_type'] = $values['document_type'];
        $this->data['document'] = $values['document'];
        $this->data['name'] = $values['name'];
        $this->data['lastname'] = $values['lastname'];
        $this->data['email'] = $values['email'];
        $this->data['cellphone'] = $values['phone'];
        $this->data['status'] = $values['terms'];
        break;
    }

    // Validations.
    foreach ($this->data as $key => $value) {
      switch ($key) {
        case 'document':
          // The field document is required.
          $this->validateFieldRequired($value);
          break;

        case 'name':
          // The field name is required.
          $this->validateFieldRequired($value);
          break;

        case 'lastname':
          // The field lastname is required.
          $this->validateFieldRequired($value);
          break;

        case 'email':
          // The field email is required and validate format.
          $this->validateFieldRequired($value);
          $this->validateFieldEmail($value);

          break;

        case 'cellphone':
          // The field cellphone is required.
          $this->validateFieldRequired($value);
          $this->validateFieldNumeric($key, $value);
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
      global $base_url;
      $result = ['Codmensaje' => 'ErrorIP'];

      // The customer data.
      $docType = isset($this->data['document_type']) ? $this->data['document_type'] : NULL;
      $document = isset($this->data['document']) ? $this->data['document'] : NULL;
      $name = isset($this->data['name']) ? $this->data['name'] : NULL;
      $lastname = isset($this->data['lastname']) ? $this->data['lastname'] : NULL;
      $email = isset($this->data['email']) ? $this->data['email'] : NULL;
      $cellphone = isset($this->data['cellphone']) ? $this->data['cellphone'] : NULL;

      // Authorization data.
      $date = date('Y-m-d\TH:i:s');
      $business = $this->business;
      $canal = $this->canal;
      $tyc = $this->getContentTermsAndConditions();
      $proccess = $this->data['form_name'];
      $uri = \Drupal::request()->getRequestUri();
      $status = $this->data['status'] ? 1 : 2;

      // Unfunctional data.
      $url = strpos($uri, 'system/ajax') ? $_SERVER['HTTP_REFERER'] : $base_url . $uri;
      $ip = \Drupal::request()->getClientIp();
      $system = $this->system;

      if ($canal && $proccess && $ip && $system) {
        // Structure to send.
        $header = "<Header>";
        $header .= "<prot:Canal>{$canal}</prot:Canal>";
        $header .= "<prot:ProcesoPeticion>{$proccess}</prot:ProcesoPeticion>";
        $header .= "<prot:IpPeticion>{$ip}</prot:IpPeticion>";
        $header .= "<prot:SistemaOrigen>{$system}</prot:SistemaOrigen>";
        $header .= "<prot:UrlSistema>{$url}</prot:UrlSistema>";
        $header .= "</Header>";
        $body = "<Body>";
        $body .= "<prot:TipoIdentificacion>{$docType}</prot:TipoIdentificacion>";
        $body .= "<prot:NumeroIdentificacion>{$document}</prot:NumeroIdentificacion>";
        $body .= "<prot:Nombres>{$name}</prot:Nombres>";
        $body .= "<prot:Apellidos>{$lastname}</prot:Apellidos>";
        $body .= "<prot:CorreoElectronico>{$email}</prot:CorreoElectronico>";
        $body .= "<prot:NumeroTelefonoCelular>{$cellphone}</prot:NumeroTelefonoCelular>";
        $body .= "<prot:FechaAutorizacion>{$date}</prot:FechaAutorizacion>";
        $body .= "<prot:EstadoAutorizacion>{$status}</prot:EstadoAutorizacion>";
        $body .= "<prot:UnidadDeNegocio>{$business}</prot:UnidadDeNegocio>";
        $body .= "<prot:TextoTerminos>tyc</prot:TextoTerminos>";
        $body .= "</Body>";
        $params = "<prot:ProteccionDatosRqType>{$header}{$body}</prot:ProteccionDatosRqType>";

        try {
          // Web service Call.
          $client = new \nusoap_client($this->wsUrl, TRUE);
          $client->soap_defencoding = 'UTF-8';
          $client->namespaces['prot'] = $this->wsNs;
          $result = $client->call('WS_PROTECCION_DATOS', $params);
        }
        catch (Exception $e) {
          // @Watchdog
          \Drupal::logger('habeas_data')->notice('Respuesta WS Habeas Data: ' . $result['Codmensaje']);
        }
      }

      if ($this->debug) {
        // Log parameters sended.
        \Drupal::logger('habeas_data')->notice('Parametros enviados: ' . htmlspecialchars($params));
        // Log response.
        \Drupal::logger('habeas_data')->notice('Result: ' . $result['Codmensaje']);
      }

      $codes_accepted = ['PD001', 'PD002', 'PD003', 'PD004'];
      if (isset($result['Codmensaje']) && in_array($result['Codmensaje'], $codes_accepted)) {
        // The data has been saved.
        /* Some code here. */
      }
      else {
        // An error has occurred.
        $this->error = TRUE;

        // Send information about the error.
        $params = '<code_response>' . $result['Codmensaje'] . '</code_response>' . $params;
        \Drupal::logger('habeas_data')->error('Error ' . htmlspecialchars($params));
      }
    }
  }

  /**
   * Function getContentTermsAndConditions().
   *
   * Get data to terms and conditios page configurated.
   */
  private function getContentTermsAndConditions() {
    $node = \Drupal::entityManager()->getStorage('node')->load($this->nidTyc);
    $node_tyc_content = $node->body->getValue();
    $content = html_entity_decode($node_tyc_content[0]['value'], ENT_COMPAT, 'UTF-8');
    $node_tyc_content = strip_tags($content);
    $node_tyc_content = substr($node_tyc_content, 0, 3999);

    return $node_tyc_content;
  }

}
