<?php

namespace Drupal\mynotes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\node\Entity\Node;
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
  private $request;

  /**
   * ActionController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(RequestStack $request_stack) {
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }

  /**
   * Update node fields.
   *
   * Performs actions: Add star/Remove star and Archive/Unarchive.
   */
  public function perform($fieldname, $nid) {
    $node = Node::load($nid);
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
