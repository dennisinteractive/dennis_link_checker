<?php

namespace Drupal\dennis_link_checker\Form;

use Drupal\Core\State\State;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LinkCheckerLinkConfigForm
 *
 * @package Drupal\dennis_link_checker\Form
 */
class LinkCheckerLinkConfigForm extends FormBase {

  /**
   * @var \Drupal\Core\State\State
   */
  protected $state;

  /**
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * LinkCheckerLinkConfigForm constructor.
   *
   * @param State $state
   * @param Messenger $messenger
   */
  public function __construct(State $state,
                              Messenger $messenger) {
    $this->state = $state;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
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
    return 'link_checker_link_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {


    $options = [
      1 => $this->t('Yes (Internal Links Only).'),
      0 => $this->t('No (Include external Links).'),
    ];

    $defaultValue = 1;
    if ($this->state->get('dennis_link_checker_link_internal', 1) == 0) {
      $defaultValue = $this->state->get('dennis_link_checker_link_internal');
    }

    $form['set_internal'] = [
      '#type' => 'radios',
      '#title' => t('Check Internal Links Only'),
      '#options' => $options,
      '#default_value' => $defaultValue,
    ];
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
    $this->state->set('dennis_link_checker_link_internal', $form_state->getValue('set_internal'));
    if ($form_state->getValue('set_internal') == 1) {
      $message = 'The Link checker has been set to check internal links only.';
    } else {
      $message = 'The Link checker has been set check internal and external links.';
    }
    $this->messenger->addMessage(t('@message',
      [
        '@message' => $message,
      ]
    ));
  }
}
