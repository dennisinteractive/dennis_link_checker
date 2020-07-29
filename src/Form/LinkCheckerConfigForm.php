<?php

namespace Drupal\dennis_link_checker\Form;


use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LinkCheckerConfigForm
 *
 * @package Drupal\dennis_link_checker\Form
 */
class LinkCheckerConfigForm extends ConfigFormBase {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * LinkCheckerConfigForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Messenger\Messenger $messenger
   */
  public function __construct(ConfigFactoryInterface $config_factory, Messenger $messenger) {
    parent::__construct($config_factory);
    $this->configFactory = $config_factory;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('messenger')
    );
  }

  protected function getEditableConfigNames() {
    return ['dennis_link_checker.settings'];
  }

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'link_checker_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $defaultValue = $this->config('dennis_link_checker.settings')->get('link_checker_site_url');

    $form['link_checker_site_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Site URL: (protocol not required)'),
      '#default_value' => $defaultValue,
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,

    );
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $link_checker_site_url = $form_state->getValue('link_checker_site_url');

    parent::submitForm($form, $form_state);
    // Saving the module configuration.
    $this->config('dennis_link_checker.settings')
      ->set('link_checker_site_url', $link_checker_site_url)
      ->save();
    $this->messenger->addMessage($this->t('The Site URL for checking has been updated.'));
  }
}
