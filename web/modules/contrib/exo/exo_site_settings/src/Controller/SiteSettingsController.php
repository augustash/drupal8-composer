<?php

namespace Drupal\exo_site_settings\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\exo_site_settings\Entity\SiteSettingsTypeInterface;

/**
 * Class SiteSettingsController.
 */
class SiteSettingsController extends ControllerBase {

  /**
   * Collection list for config page.
   *
   * @return array
   *   An array suitable for rendering.
   */
  public function collection() {
    if (!$this->entityTypeManager()->getStorage('exo_site_settings')->hasNonAggregated()) {
      return $this->formBuilder()->getForm('\Drupal\exo_site_settings\Form\SiteSettingsGeneralForm');
    }
    return $this->entityTypeManager()->getListBuilder('exo_site_settings')->render();
  }

  /**
   * Create/edit form for a config page.
   *
   * @return array
   *   An array suitable for rendering.
   */
  public function form(SiteSettingsTypeInterface $exo_site_settings_type) {
    $exo_site_settings = $this->entityTypeManager()->getStorage('exo_site_settings')->loadOrCreateByType($exo_site_settings_type->id());
    return $this->entityFormBuilder()->getForm($exo_site_settings);
  }

  /**
   * Create/edit form for a config page title.
   *
   * @return string
   *   Return Hello string.
   */
  public function formTitle(SiteSettingsTypeInterface $exo_site_settings_type) {
    return $this->t('@label Settings', [
      '@label' => $exo_site_settings_type->label(),
    ]);
  }

}
