<?php

namespace Drupal\mynotes\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use \Drupal\node\Entity\Node;

class QuickNoteForm extends FormBase {

  // Validation error messages.
  protected $errorMessages = [];

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * Constructor.
   */
  public function __construct(EntityManager $entity_manager) {
    $this->errorMessages = [
      'title.required' => $this->t('Please enter a title.'),
      'description.required' => $this->t('Please enter a description.'),
    ];

    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mynotes_quick_note';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#suffix' => '<span id="validation-message-title" class="validation-message"></span>',
    );

    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => t('Description'),
      '#suffix' => '<span id="validation-message-description" class="validation-message"></span>',
    );

    $form['label'] = array(
      '#type' => 'select',
      '#title' => t('Label'),
      '#options' => $this->getLabelOptions(),
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
      '#ajax' => array(
        'callback' => array($this, 'validateFormAjax'),
        'progress' => array(
          'type' => 'throbber',
        ),
      ),
    );

    $form['messages'] = array(
      '#type' => '#markup',
      '#markup' => '<div id="success-message-saved" class="success-message"></div>'
    );

    return $form;
  }

  protected function getLabelOptions() {
    $label_terms = $this->entityManager->getStorage('taxonomy_term')
      ->loadTree('labels');
    $labels = ['- None -'];

    foreach ($label_terms as $label) {
      $labels[$label->tid] = $label->name;
    }

    return $labels;
  }

  /**
   * Validate the entire form via AJAX. If validation is passed, the submit
   * method is called.
   */
  public function validateFormAjax(array &$form, FormStateInterface $form_state) {
    $has_errors = FALSE;
    $response = new AjaxResponse();
    // Validate each of the form fields.
    $title_validation = $this->validateTitle($form, $form_state);
    $description_validation = $this->validateDescription($form, $form_state);
    // Initialize message variables.
    $title_message = '';
    $description_message = '';
    // Check the validations of each field, and retrieve the error message
    // if present.
    if (!$title_validation['is_valid']) {
      $title_message = $title_validation['message'];
      $has_errors = TRUE;
    }
    if (!$description_validation['is_valid']) {
      $description_message = $description_validation['message'];
      $has_errors = TRUE;
    }

    if ($has_errors) {
      // Display validation error messages.
      $response->addCommand(new HtmlCommand('#validation-message-title', $title_message));
      $response->addCommand(new HtmlCommand('#validation-message-description', $description_message));
      return $response;
    }
    else {
      return $this->submitFormAjax($form, $form_state);
    }
  }

  /**
   * Validate title.
   */
  protected function validateTitle(array &$form, FormStateInterface $form_state) {
    $validation  = [
      'is_valid' => TRUE,
      'message' => ''
    ];
    $title = $form_state->getValue('title');
    if (!$title) {
      $validation['is_valid'] = FALSE;
      $validation['message'] = $this->errorMessages['title.required'];
    }
    return $validation;
  }

  /**
   * Validate description.
   */
  protected function validateDescription(array &$form, FormStateInterface $form_state) {
    $validation  = [
      'is_valid' => TRUE,
      'message' => ''
    ];
    $description = $form_state->getValue('description');
    if (!$description) {
      $validation['is_valid'] = FALSE;
      $validation['message'] = $this->errorMessages['description.required'];
    }
    return $validation;
  }

  /**
   * Submit data.
   */
  public function submitFormAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $title = $form_state->getValue('title');
    $description = $form_state->getValue('description');
    $label = $form_state->getValue('label');
    $node = Node::create([
      'type' => 'note',
      'title' => $title,
      'body' => [
        'value' => $description,
        'format' => 'basic_html',
      ],
      'field_labels' => ['target_id' => $label],
    ]);
    $node->save();

    $response->addCommand(new InvokeCommand('#edit-title', 'val', ['']));
    $response->addCommand(new InvokeCommand('#edit-description', 'val', ['']));
    $response->addCommand(new HtmlCommand('#validation-message-title', ''));
    $response->addCommand(new HtmlCommand('#validation-message-description', ''));
    $response->addCommand(new HtmlCommand('#success-message-saved', 'Note successfully added.'));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    drupal_set_message("Hi! I'm a form within a block.");
  }

  protected function clearFormInput(array $form, FormStateInterface $form_state) {
    // Replace the form entity with an empty instance.

    // Clear user input.
    $input = $form_state->getUserInput();
    // We should not clear the system items from the user input.
    $clean_keys = $form_state->getCleanValueKeys();
    $clean_keys[] = 'ajax_page_state';
    foreach ($input as $key => $item) {
      if (!in_array($key, $clean_keys) && substr($key, 0, 1) !== '_') {
        unset($input[$key]);
      }
    }
    $form_state->setUserInput($input);
    // Rebuild the form state values.
    $form_state->setRebuild();
    $form_state->setStorage([]);
  }

}