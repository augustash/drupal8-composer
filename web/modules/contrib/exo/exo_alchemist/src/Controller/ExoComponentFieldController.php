<?php

namespace Drupal\exo_alchemist\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ExoComponentFieldController.
 */
class ExoComponentFieldController extends ControllerBase {

  /**
   * The eXo component field manager.
   *
   * @var \Drupal\exo_alchemist\ExoComponentFieldManager
   */
  protected $exoComponentFieldManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->exoComponentFieldManager = $container->get('plugin.manager.exo_component_field');
    return $instance;
  }

  /**
   * Fieldlist.
   *
   * @return string
   *   Return Hello string.
   */
  public function fieldList() {
    $build = [];

    $delta = 0;
    foreach ($this->exoComponentFieldManager->getGroupedDefinitions() as $label => $definitions) {
      $build[$delta]['group'] = [
        '#type' => 'details',
        '#title' => $label ?: $this->t('Global'),
        '#open' => empty($label),
      ];

      $build[$delta]['group']['table'] = [
        '#type' => 'table',
        '#header' => [
          'id' => $this->t('ID'),
          'label' => $this->t('Label'),
          'context' => $this->t('Contexts'),
        ],
      ];
      foreach ($definitions as $key => $definition) {
        $row = [];
        $row[] = $key;
        $row[] = $definition['label'];
        $contexts = [];
        if (!empty($definition['context_definitions'])) {
          foreach ($definition['context_definitions'] as $id => $context) {
            /** @var \Drupal\Core\Plugin\Context\ContextDefinitionInterface $context */
            $label = $context->getLabel() ?: $id;
            $contexts[] = $label . ' (' . $context->getDataType() . ')';
            foreach ($context->getConstraints() as $label => $constraint) {
              $contexts[] = $label . ' (' . implode(', ', $constraint) . ')';
            }
          }
        }
        $row[] = implode(', ', $contexts);
        $build[$delta]['group']['table']['#rows'][] = $row;
      }

      $delta++;
    }
    $field_definitions = $this->exoComponentFieldManager->getDefinitions();

    return $build;
  }

}
