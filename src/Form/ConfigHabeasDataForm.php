<?php

/**
 * @file
 * Contains \Drupal\clc_web_services\Form\ConfigHabeasDataForm.
 */

namespace Drupal\clc_web_services\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class ConfigHabeasDataForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    // Unique ID of the form.
    return 'config_habeas_data_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::state()->get('config_habeas_data', NULL);

    $form['ws'] = [
      '#type' => 'details',
      '#title' => $this->t('Web Services Information'),
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
      'business' => [
        '#type' => 'radios',
        '#title' => $this->t('Business'),
        '#options' => [
          'N01' => 'AKT',
          'N02' => 'Kalley',
          'N04' => 'Alkomprar',
          'N05' => 'Distribuciones',
          'N06' => 'Nariñenses lo máximo',
          'N08' => 'Textiles Corbeta',
          'N09' => 'Spartan',
          'N10' => 'Royald Enfield',
        ],
        '#attributes' => ['class' => ['field-business']],
        '#default_value' => isset($config['business']) ? $config['business'] : NULL,
      ],
      'terms' => [
        '#type' => 'textfield',
        '#title' => $this->t('Terms and conditions'),
        '#autocomplete_route_name' => 'clc_web_services.autocomplete_contents',
        '#placeholder' => $this->t('Enter the content name of terms and conditions'),
        '#description' => $this->t('Just accept contents of type basic page'),
        '#default_value' => isset($config['terms']) ? $config['terms'] : NULL,
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
    // Get nid term and conditions.
    $terms = $form_state->getValue('terms');
    preg_match('/\((.+)\)/', $terms, $nid);
    $nid = $nid[1];

    // Save the configuration.
    $config = [
      'nid_terms' => $nid,
      'terms' => $values['terms'],
      'business' => $values['business'],
      'ws_endpoint' => $values['ws_endpoint'],
      'ws_namespace' => $values['ws_namespace'],
      'ws_debug' => $values['ws_debug'],
    ];
    \Drupal::state()->set('config_habeas_data', $config);
  }
}
