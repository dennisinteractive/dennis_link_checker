<?php

namespace Drupal\dennis_link_checker\Form;

use Drupal\Core\State\State;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LinkCheckerConfigForm
 *
 * @package Drupal\dennis_link_checker\Form
 */
class LinkCheckerConfigForm extends FormBase {

  /**
   * @var RequestStack
   */
  protected $request;

  /**
   * @var \Drupal\Core\State\State
   */
  protected $state;

  /**
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * LinkCheckerConfigForm constructor.
   *
   * @param RequestStack $request
   * @param State $state
   * @param Messenger $messenger
   */
  public function __construct(RequestStack $request,
                              State $state,
                              Messenger $messenger) {
    $this->request = $request;
    $this->state = $state;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('state'),
      $container->get('messenger')
    );
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

    $default_site_url = $this->request->getCurrentRequest()->getHttpHost();
    $defaultValue = $this->state->get('dennis_link_checker_site_url', $default_site_url);

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
    $this->state->set('dennis_link_checker_site_url', $form_state->getValue('link_checker_site_url'));
    $this->messenger->addMessage($this->t('The Site URL for checking has been updated.'));
  }
}
