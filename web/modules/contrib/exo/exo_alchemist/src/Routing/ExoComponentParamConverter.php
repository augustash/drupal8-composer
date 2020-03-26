<?php

namespace Drupal\exo_alchemist\Routing;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Drupal\exo_alchemist\ExoComponentManager;
use Symfony\Component\Routing\Route;

/**
 * Class ExoComponentParamConverter.
 */
class ExoComponentParamConverter implements ParamConverterInterface {

  /**
   * The eXo component plugin manager.
   *
   * @var \Drupal\exo_alchemist\ExoComponentManager
   */
  protected $exoComponentManager;

  /**
   * Constructs a new ExoComponentParamConverter object.
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
  public function convert($value, $definition, $name, array $defaults) {
    $op = $definition['exo_component_plugin'];
    if ($op == 'create') {
      if ($this->exoComponentManager->hasDefinition($value)) {
        return $this->exoComponentManager->getDefinition($value);
      }
    }
    if ($this->exoComponentManager->hasInstalledDefinition($value)) {
      return $this->exoComponentManager->getInstalledDefinition($value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return !empty($definition['exo_component_plugin']);
  }

}
