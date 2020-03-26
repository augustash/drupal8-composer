<?php

namespace Drupal\exo_toolbar\Cache\Context;

use Drupal\exo_toolbar\Routing\ExoToolbarPathMatcherInterface;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * Defines a cache context for whether the URL is within the eXo toolbar admin.
 *
 * Cache context ID: 'url.path.is_exo_toolbar_admin'.
 */
class IsExoToolbarAdminPathCacheContext implements CacheContextInterface {

  /**
   * The eXo toolbar path matcher service.
   *
   * @var \Drupal\exo_toolbar\Routing\ExoToolbarPathMatcherInterface
   */
  protected $exoToolbarPathMatcher;

  /**
   * Constructs an IsExoToolbarAdminPathCacheContext object.
   *
   * @param \Drupal\exo_toolbar\Routing\ExoToolbarPathMatcherInterface $exo_toolbar_path_matcher
   *   The eXo toolbar path matcher.
   */
  public function __construct(ExoToolbarPathMatcherInterface $exo_toolbar_path_matcher) {
    $this->exoToolbarPathMatcher = $exo_toolbar_path_matcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Is eXo toolbar admin page');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    return 'is_exo_toolbar_admin.' . (int) $this->exoToolbarPathMatcher->isAdmin();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
