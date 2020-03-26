<?php

namespace Drupal\exo_alchemist\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\exo_alchemist\Definition\ExoComponentDefinition;
use Symfony\Component\Routing\Route;

/**
 * Provides a generic access checker for entities.
 */
class ExoComponentAccessCheck implements AccessInterface {

  /**
   * Checks access to the entity operation on the given route.
   *
   * The route's '_entity_access' requirement must follow the pattern
   * 'entity_stub_name.operation', where available operations are:
   * 'view', 'update', 'create', and 'delete'.
   *
   * For example, this route configuration invokes a permissions check for
   * 'update' access to entities of type 'node':
   * @code
   * pattern: '/foo/{node}/bar'
   * requirements:
   *   _entity_access: 'node.update'
   * @endcode
   * And this will check 'delete' access to a dynamic entity type:
   * @code
   * example.route:
   *   path: foo/{parameter}/{example}
   *   requirements:
   *     _entity_access: example.delete
   *   options:
   *     parameters:
   *       example:
   *         type: entity:{parameter}
   * @endcode
   * The route match parameter corresponding to the stub name is checked to
   * see if it is entity-like i.e. implements EntityInterface.
   *
   * @see \Drupal\Core\ParamConverter\EntityConverter
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The parametrized route.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {
    // Split the entity type and the operation.
    $requirement = $route->getRequirement('_exo_component');
    list($parameter, $operation) = explode('.', $requirement);
    // If $parameter parameter is a valid entity, call its own access check.
    $parameters = $route_match->getParameters();
    if ($parameters->has($parameter)) {
      $definition = $parameters->get($parameter);
      if ($definition instanceof ExoComponentDefinition) {
        return \Drupal::service('plugin.manager.exo_component')->accessDefinition($definition, $operation, $account);
      }
    }
    return AccessResult::neutral();
  }

}
