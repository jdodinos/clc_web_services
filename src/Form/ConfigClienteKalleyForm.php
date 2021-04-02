<?php

/**
 * @file
 * Contains \Drupal\clc_web_services\Form\ConfigClienteKalleyForm.
 */

namespace Drupal\clc_web_services\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class ConfigClienteKalleyForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    // Unique ID of the form.
    return 'config_cliente_kalley_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::state()->get('config_cliente_kalley_services', NULL);

    $form['ws'] = [
      '#type' => 'details',
      '#title' => $this->t('Web Services - vozclientekalley'),
      '#open' => TRUE,
      'ws_endpoint' => [
        '#type' => 'textfield',
        '#title' => $this->t('Endpoint'),
        '#placeholder' => $this->t('Please the url endpoint'),
        '#attributes' => ['class' => ['field-endpoint']],
        '#default_value' => isset($config['ws_endpoint']) ? $config['ws_endpoint'] : NULL,
      ],
      'ws_environment' => [
        '#type' => 'radios',
        '#title' => $this->t('Server'),
        '#attributes' => ['class' => ['field-ws-server']],
        '#options' => [
          'env_development' => $this->t('development environment'),
          'env_production' => $this->t('production environment'),
        ],
        '#default_value' => isset($config['ws_environment']) ? $config['ws_environment'] : 'env_development',
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
      'ws_environment' => $values['ws_environment'],
      'ws_debug' => $values['ws_debug'],
    ];
    \Drupal::state()->set('config_cliente_kalley_services', $config);
  }
}
