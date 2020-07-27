<?php

namespace Drupal\exo\Plugin;

use Drupal\Component\Plugin\Mapper\MapperInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Routing\AdminContext;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Gathers the throbber plugins.
 */
class ExoThrobberManager extends DefaultPluginManager implements ExoThrobberManagerInterface, MapperInterface {

  /**
   * The admin context.
   *
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected $adminContext;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * The config interface.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, AdminContext $admin_context, RequestStack $request_stack, ConfigFactoryInterface $config_factory) {
    parent::__construct('Plugin/ExoThrobber', $namespaces, $module_handler, 'Drupal\exo\Plugin\ExoThrobberPluginInterface', 'Drupal\exo\Annotation\ExoThrobber');
    $this->adminContext = $admin_context;
    $this->configFactory = $config_factory;
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function getThrobberOptionList() {
    $options = [];
    foreach ($this->getDefinitions() as $definition) {
      $options[$definition['id']] = $definition['label'];
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function loadThrobberInstance($plugin_id) {
    return $this->createInstance($plugin_id);
  }

  /**
   * {@inheritdoc}
   */
  public function loadAllThrobberInstances() {
    $throbbers = [];
    foreach ($this->getDefinitions() as $definition) {
      array_push($throbbers, $this->createInstance($definition['id']));
    }

    return $throbbers;
  }

  /**
   * Function to check if Route is Applicable.
   *
   * @{inheritdoc}
   * @codingStandardsIgnoreStart
   */
  public function routeIsApplicable() {
    // @codingStandardsIgnoreEnd
    $is_applicable = FALSE;
    $settings = $this->configFactory->get('exo.loader');
    $is_admin_route = $this->adminContext->isAdminRoute();
    $current_route_name = $this->request->attributes->get('_route');

    if (!$is_admin_route) {
      // Always applicable.
      $is_applicable = TRUE;
    }
    elseif ($settings->get('show_admin_paths') && $current_route_name != 'exo.loader') {
      $is_applicable = TRUE;
    }

    return $is_applicable;
  }

}
