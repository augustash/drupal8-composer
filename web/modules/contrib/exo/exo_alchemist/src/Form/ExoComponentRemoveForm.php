<?php

namespace Drupal\exo_alchemist\Form;

use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\exo_alchemist\Ajax\ExoComponentBlur;
use Drupal\layout_builder\Form\RemoveBlockForm;
use Drupal\layout_builder\SectionStorageInterface;

/**
 * Provides a form to confirm the removal of a block.
 *
 * @internal
 *   Form classes are internal.
 */
class ExoComponentRemoveForm extends RemoveBlockForm {

  /**
   * Rebuilds the layout.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response to either rebuild the layout and close the dialog, or
   *   reload the page.
   */
  protected function rebuildAndClose(SectionStorageInterface $section_storage) {
    $response = $this->rebuildLayout($section_storage);
    $response->addCommand(new CloseDialogCommand('#drupal-off-canvas'));
    $response->addCommand(new ExoComponentBlur());
    return $response;
  }

}
