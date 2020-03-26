<?php

namespace Drupal\exo_alchemist\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\exo_alchemist\Definition\ExoComponentDefinition;
use Drupal\exo_alchemist\ExoComponentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ExoComponentLibraryController.
 */
class ExoComponentLibraryController extends ControllerBase {

  /**
   * The eXo component plugin manager.
   *
   * @var \Drupal\exo_alchemist\ExoComponentManager
   */
  protected $exoComponentManager;

  /**
   * Constructs a new DeleteMultiple object.
   *
   * @param \Drupal\exo_alchemist\ExoComponentManager $exo_component_manager
   *   The eXo component manager.
   */
  public function __construct(ExoComponentManager $exo_component_manager) {
    $this->exoComponentManager = $exo_component_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.exo_component')
    );
  }

  /**
   * List all available components.
   */
  public function listComponents() {
    $build = [];

    $build['components'] = [
      '#type' => 'table',
      '#header' => [
        'thumb' => '',
        'component' => $this->t('Component'),
        'version' => $this->t('Version'),
        'installed_version' => $this->t('Installed Version'),
        'operations' => '',
      ],
    ];
    $definitions = $this->exoComponentManager->getDefinitions();
    // ksm($definitions);
    $installed_definitions = $this->exoComponentManager->getInstalledDefinitions();
    $all_definitions = $definitions + array_diff_key($installed_definitions, $definitions);
    foreach ($all_definitions as $id => $definition) {
      /** @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition */
      if ($definition->isHidden()) {
        continue;
      }
      $row = [];
      $row['thumbnail'] = [];
      if (!$definition->isMissing() && ($thumbnail = $definition->getThumbnailSource())) {
        $row['thumbnail'] = [
          '#type' => 'inline_template',
          '#template' => '<img src="{{ image }}" style="width:60px; height: auto;" />',
          '#wrapper_attributes' => ['style' => 'min-width:60px; width:1%;'],
          '#context' => [
            'image' => $thumbnail,
          ],
        ];
      }
      $row['component'] = [
        '#type' => 'inline_template',
        '#template' => '<strong>{{ title }}</strong> ({{ provider }})<br><small>{{ description }}</small>',
        '#context' => [
          'title' => $definition->getLabel(),
          'provider' => $definition->getProvider(),
          'description' => $definition->getDescription(),
        ],
      ];
      $row['version']['#markup'] = $definition->isMissing() ? $this->t('Missing') : $definition->getVersion();
      $row['installed_version']['#markup'] = '-';

      $row['operations'] = [
        '#type' => 'operations',
        '#links' => [],
      ];

      if (isset($installed_definitions[$id])) {
        $installed_definition = $installed_definitions[$id];
        $row['installed_version']['#markup'] = $installed_definition->getVersion();
        if ($this->exoComponentManager->accessDefinition($installed_definition, 'update')->isAllowed()) {
          $row['operations']['#links']['update'] = [
            'title' => $this->t('Update'),
            'url' => Url::fromRoute('exo_alchemist.component.update', [
              'definition' => $definition->id(),
            ]),
          ];
        }
        if ($this->exoComponentManager->accessDefinition($installed_definition, 'view')->isAllowed()) {
          $row['operations']['#links']['preview'] = [
            'title' => $this->t('Preview'),
            'url' => Url::fromRoute('exo_alchemist.component.preview', [
              'definition' => $definition->id(),
            ]),
          ];
        }
        $row['operations']['#links']['uninstall'] = [
          'title' => $this->t('Uninstall'),
          'url' => Url::fromRoute('exo_alchemist.component.uninstall', [
            'definition' => $definition->id(),
          ]),
        ];
      }
      else {
        $row['operations']['#links']['install'] = [
          'title' => $this->t('Install'),
          'url' => Url::fromRoute('exo_alchemist.component.install', [
            'definition' => $definition->id(),
          ]),
        ];
      }

      $build['components'][$id] = $row;
    }

    return $build;
  }

  /**
   * Refresh components.
   */
  public function refreshComponents() {
    $this->exoComponentManager->clearCachedDefinitions();
    return $this->redirect('exo_alchemist.component.collection');
  }

}
