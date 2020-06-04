<?php

namespace Drupal\mynotes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ActionController.
 *
 * @package Drupal\mynotes\Controller
 */
class ActionController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ActionController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(RequestStack $request_stack, EntityTypeManagerInterface $entity_type_manager) {
    $this->request = $request_stack->getCurrentRequest();
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Update node fields.
   *
   * Performs actions: Add star/Remove star and Archive/Unarchive.
   */
  public function perform($fieldname, $nid) {
    $node = $this->entityTypeManager
      ->getStorage('node')
      ->load($nid);
    $fieldname = 'field_' . $fieldname;
    $current_field_value = $node->{$fieldname}->value;
    $node->{$fieldname}->value = !$current_field_value;
    $node->save();

    $previous_url = $this->request->server->get('HTTP_REFERER');
    if (!$previous_url) {
      $previous_url = '/notes';
    }
    return new RedirectResponse($previous_url);
  }

}
