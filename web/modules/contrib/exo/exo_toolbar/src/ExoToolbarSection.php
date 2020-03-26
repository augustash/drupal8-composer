<?php

namespace Drupal\exo_toolbar;

/**
 * Provides a toolbar section for use within toolbar regions.
 */
class ExoToolbarSection implements ExoToolbarSectionInterface {

  /**
   * The section id.
   *
   * @var string
   */
  protected $id;

  /**
   * The section label.
   *
   * @var string
   */
  protected $label;

  /**
   * The section sort order.
   *
   * @var string
   */
  protected $sort;

  /**
   * Constructs a new ExoToolbarSection object.
   *
   * @param string $id
   *   The section id.
   * @param string $label
   *   The human-readable section label.
   * @param string $sort
   *   Sort order. Either asc or desc.
   */
  public function __construct($id, $label, $sort = 'asc') {
    $this->id = $id;
    $this->label = $label;
    $this->setSort($sort);
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function getSort() {
    return $this->sort;
  }

  /**
   * {@inheritdoc}
   */
  public function setSort($sort) {
    $this->sort = $sort == 'desc' ? 'desc' : 'asc';
    return $this;
  }

}
