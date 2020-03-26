<?php

namespace Drupal\exo_toolbar;

/**
 * Defines an interface for eXo toolbar section.
 */
interface ExoToolbarSectionInterface {

  /**
   * Get section id.
   *
   * @return string
   *   The section id.
   */
  public function id();

  /**
   * Get section label.
   *
   * @return string
   *   The section human-readable label.
   */
  public function label();

  /**
   * Get section sort order.
   *
   * @return string
   *   Return either asc or desc.
   */
  public function getSort();

  /**
   * Set section sort order.
   *
   * @var string $sort
   *   Either asc or desc.
   *
   * @return $this
   */
  public function setSort($sort);

}
