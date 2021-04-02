<?php

/**
 * @file
 * Contains \Drupal\clc_web_services\Form\ConfigCrmServiceForm.
 */

namespace Drupal\clc_web_services\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class ConfigCrmServiceForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    // Unique ID of the form.
    return 'config_crm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::state()->get('config_crm_services', NULL);

    $form['ws'] = [
      '#type' => 'details',
      '#title' => $this->t('Web Services - crea_caso'),
      '#open' => TRUE,
      'ws_endpoint' => [
        '#type' => 'textfield',
        '#title' => $this->t('Endpoint'),
        '#placeholder' => $this->t('Please the url endpoint'),
        '#attributes' => ['class' => ['field-endpoint']],
        '#default_value' => isset($config['ws_endpoint']) ? $config['ws_endpoint'] : NULL,
      ],
      'ws_namespace' => [
        '#type' => 'textfield',
        '#title' => $this->t('PQR Namespace'),
        '#placeholder' => $this->t('Please enter the Web Service namespace PROT'),
        '#attributes' => ['class' => ['field-ws-namespace']],
        '#default_value' => isset($config['ws_namespace']) ? $config['ws_namespace'] : NULL,
      ],
      'ws_user' => [
        '#type' => 'textfield',
        '#title' => $this->t('Username'),
        '#placeholder' => $this->t('Please enter the USERNAME'),
        '#attributes' => ['class' => ['field-ws-username']],
        '#default_value' => isset($config['ws_user']) ? $config['ws_user'] : NULL,
      ],
      'ws_password' => [
        '#type' => 'textfield',
        '#title' => $this->t('Password'),
        '#placeholder' => $this->t('Please enter the PASSWORD'),
        '#attributes' => ['class' => ['field-ws-password']],
        '#default_value' => isset($config['ws_password']) ? $config['ws_password'] : NULL,
      ],
      'ws_debug' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Activate debug'),
        '#attributes' => ['class' => ['field-ws-debug']],
        '#default_value' => isset($config['ws_debug']) ? $config['ws_debug'] : NULL,
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Form values.
    $values = $form_state->getValues();

    // Save the configuration.
    $config = [
      'ws_endpoint' => $values['ws_endpoint'],
      'ws_namespace' => $values['ws_namespace'],
      'ws_user' => $values['ws_user'],
      'ws_password' => $values['ws_password'],
      'ws_debug' => $values['ws_debug'],
    ];
    \Drupal::state()->set('config_crm_services', $config);
  }
}
