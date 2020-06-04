<?php

namespace Drupal\mynotes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;

/**
 * QuickNoteController class.
 */
class QuickNoteController extends ControllerBase {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * QuickNoteController constructor.
   *
   * @param \Drupal\Core\Form\FormBuilder $formBuilder
   *   The form builder.
   */
  public function __construct(FormBuilder $formBuilder) {
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('form_builder'));
  }

  /**
   * Open quick note form in modal.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The Ajax response.
   */
  public function openModalForm() {
    $response = new AjaxResponse();
    $quick_note_form = $this->formBuilder->getForm('Drupal\mynotes\Form\QuickNoteForm');
    $response->addCommand(new OpenModalDialogCommand('Quick note', $quick_note_form, ['width' => '800']));
    return $response;
  }

}
