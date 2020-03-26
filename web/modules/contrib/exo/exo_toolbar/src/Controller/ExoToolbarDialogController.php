<?php

namespace Drupal\exo_toolbar\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\exo_toolbar\Entity\ExoToolbarItemInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\exo_toolbar\Plugin\ExoToolbarItemDialogPluginInterface;

/**
 * Class ExoToolbarDialogController.
 */
class ExoToolbarDialogController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new ExoToolbarDialogController object.
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
   * View dialog content.
   *
   * @param \Drupal\exo_toolbar\Entity\ExoToolbarItemInterface $exo_toolbar_item
   *   The eXo toolbar item.
   * @param string $arg
   *   An optional argument.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function view(ExoToolbarItemInterface $exo_toolbar_item, $arg = NULL) {
    $plugin = $exo_toolbar_item->getPlugin();
    if ($plugin instanceof ExoToolbarItemDialogPluginInterface) {
      $dialog_type = $plugin->getDialogType();
      // Offload response generation to dialog plugin.
      return $dialog_type->dialogResponse($exo_toolbar_item, $arg);
    }
    return new AjaxResponse();
  }

}
