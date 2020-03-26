<?php

namespace Drupal\exo_modal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Entity\EntityInterface;
use Drupal\exo_modal\Ajax\ExoModalInsertCommand;
use Drupal\views\ViewEntityInterface;
use Drupal\exo_modal\Ajax\ExoModalContentCommand;

/**
 * Class ExoModalViewsController.
 */
class ExoModalViewsController extends ControllerBase {

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
  public function view(ViewEntityInterface $view, $display_id, $argument1 = NULL, $argument2 = NULL) {
    $executable = $view->getExecutable();
    $executable->setDisplay($display_id);

    $args = [];
    if ($argument1) {
      $args[] = $argument1;
    }
    if ($argument2) {
      $args[] = $argument2;
    }
    $executable->setArguments($args);
    $executable->setDisplay($display_id);
    $executable->preExecute();
    $executable->execute();
    $build = $executable->buildRenderable($display_id, $args);
    $build['#attributes']['class'][] = 'exo-modal-views-view';

    $response = new AjaxResponse();
    $response->addCommand(new ExoModalContentCommand($build));
    return $response;
  }

  /**
   * View modal content given a view field.
   */
  public function viewField(ViewEntityInterface $view, $view_display_id, $field, EntityInterface $entity, $revision_id) {
    $executable = $view->getExecutable();
    $display = $executable->getDisplay($view_display_id);
    $plugin = $display->getHandler('field', $field);

    $response = new AjaxResponse();
    // Load revision if it is not the active revision.
    if ($entity->getRevisionId() != $revision_id) {
      $entity = $this->entityTypeManager->getStorage($entity->getEntityTypeId())->loadRevision($revision_id);
    }
    $modal = $plugin->buildModal($entity);
    $modal->setSetting(['modal', 'autoOpen'], TRUE);
    $modal->setSetting(['modal', 'destroyOnClose'], TRUE);
    $response->addCommand(new ExoModalInsertCommand('body', $modal->toRenderableModal()));
    return $response;
  }

}
