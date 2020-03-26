<?php

namespace Drupal\exo_toolbar\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a eXo Toolbar Item item annotation object.
 *
 * @see \Drupal\exo_toolbar\Plugin\ExoToolbarItemManager
 * @see plugin_api
 *
 * @Annotation
 */
class ExoToolbarItem extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The administrative label of the item.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $admin_label = '';

  /**
   * A boolean stating if this item should not be sortable in the UI.
   *
   * @var bool
   */
  public $no_sort = FALSE;

  /**
   * A boolean stating that items of this type cannot be altered in admin mode.
   *
   * @var bool
   */
  public $no_admin = FALSE;

  /**
   * A boolean stating that items of this type cannot be created through the UI.
   *
   * @var bool
   */
  public $no_ui = FALSE;

  /**
   * A boolean stating that items of this type require other items.
   *
   * Dependent items require that there are other visible items within the same
   * section. If there are none, the item will not show.
   *
   * @var bool
   */
  public $is_dependent = FALSE;

}
