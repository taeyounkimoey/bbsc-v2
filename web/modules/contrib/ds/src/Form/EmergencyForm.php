<?php

namespace Drupal\ds\Form;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Emergency form for DS.
 */
class EmergencyForm extends ConfigFormBase {

  /**
   * State object.
   *
   * @var \Drupal\Core\State\State
   */
  protected $state;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a \Drupal\ds\Form\EmergencyForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key value store.
   */
  public function __construct(ConfigFactory $config_factory, ModuleHandlerInterface $module_handler, StateInterface $state) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ds_emergy_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['ds_fields_error'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Fields error'),
    ];

    $form['ds_fields_error']['disable'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('In case you get an error after configuring a layout printing a message like "Fatal error: Unsupported operand types", you can temporarily disable adding fields from DS. You probably are trying to render an node inside a node, for instance through a view, which is simply not possible. See <a href="http://drupal.org/node/1264386">http://drupal.org/node/1264386</a>.'),
    ];

    $form['ds_fields_error']['submit'] = [
      '#type' => 'submit',
      '#value' => ($this->state->get('ds.disabled', FALSE) ? $this->t('Enable attaching fields') : $this->t('Disable attaching fields')),
      '#submit' => ['::submitFieldAttach'],
      '#weight' => 1,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // empty.
  }

  /**
   * Submit callback for the fields error form.
   */
  public function submitFieldAttach(array &$form, FormStateInterface $form_state) {
    $this->state->set('ds.disabled', ($this->state->get('ds.disabled', FALSE) ? FALSE : TRUE));
    $this->messenger()->addMessage($this->t('The configuration options have been saved.'));
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ds_extras.settings',
    ];
  }

}
