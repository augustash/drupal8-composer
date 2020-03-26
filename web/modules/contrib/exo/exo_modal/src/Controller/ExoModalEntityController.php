<?php

namespace Drupal\exo_modal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Entity\EntityInterface;
use Drupal\exo_modal\Ajax\ExoModalContentCommand;

/**
 * Class ExoModalEntityController.
 */
class ExoModalEntityController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new ExoModalBlockController object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * View modal content.
   */
  public function view(EntityInterface $entity, $display_id) {
    $build = [
      'messages' => [
        '#type' => 'status_messages',
      ],
      'entity' => $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId())->view($entity, $display_id),
    ];
    $response = new AjaxResponse();
    $response->addCommand(new ExoModalContentCommand($build));
    return $response;
  }

}
