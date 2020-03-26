<?php

namespace Drupal\exo_modal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldTypePluginManager;
use Drupal\Core\Field\FormatterPluginManager;
use Drupal\exo_modal\Plugin\ExoModalFieldFormatterPluginInterface;
use Drupal\exo_modal\Ajax\ExoModalInsertCommand;

/**
 * Class ExoModalFieldFormatterController.
 */
class ExoModalFieldFormatterController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The field type plugin manager.
   *
   * @var \Drupal\Core\Field\FieldTypePluginManager
   */
  protected $fieldTypeManager;

  /**
   * The field formatter plugin manager.
   *
   * @var \Drupal\Core\Field\FormatterPluginManager
   */
  protected $fieldFormatterManager;

  /**
   * Constructs a new ExoModalBlockController object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FieldTypePluginManager $field_type_manager, FormatterPluginManager $field_formatter_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fieldTypeManager = $field_type_manager;
    $this->fieldFormatterManager = $field_formatter_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.field.field_type'),
      $container->get('plugin.manager.field.formatter')
    );
  }

  /**
   * View modal content.
   */
  public function view(EntityInterface $entity, $revision_id, $field_name, $delta, $display_id, $langcode, $display_settings = NULL) {
    $response = new AjaxResponse();
    if ($entity->hasField($field_name)) {
      $plugin = NULL;
      // Load revision if it is not the active revision.
      if ($entity->getRevisionId() != $revision_id) {
        $entity = $this->entityTypeManager->getStorage($entity->getEntityTypeId())->loadRevision($revision_id);
      }
      if ($display_id == '_custom') {
        $display_settings = json_decode(urldecode($display_settings), TRUE);
        if (is_array($display_settings)) {
          $field_definition = $entity->{$field_name}->getFieldDefinition();
          $options = [
            'field_definition' => $field_definition,
            'view_mode' => $display_id,
            'settings' => $display_settings['settings'],
            'configuration' => $display_settings,
          ];
          $plugin = $this->fieldFormatterManager->getInstance($options);
        }
      }
      else {
        $display_id = $display_id == 'full' ? 'default' : $display_id;
        $field_definition = $entity->{$field_name}->getFieldDefinition();
        $entity_view_display_id = $entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . $display_id;
        $display = $this->entityTypeManager()->getStorage('entity_view_display')->load($entity_view_display_id);
        if ($display) {
          $component = $display->getComponent($field_name);
          $options = [
            'field_definition' => $field_definition,
            'view_mode' => $display_id,
            'settings' => $component['settings'],
            'configuration' => $component,
          ];
          $plugin = $this->fieldFormatterManager->getInstance($options);
        }
      }
      if ($plugin instanceof ExoModalFieldFormatterPluginInterface) {
        $response->addCommand(new ExoModalInsertCommand('body', $plugin->buildModal($entity->{$field_name}->get($delta, $langcode), $delta, $langcode)));
      }
    }
    return $response;
  }

}
