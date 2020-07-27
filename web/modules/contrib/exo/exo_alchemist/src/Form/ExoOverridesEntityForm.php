<?php

namespace Drupal\exo_alchemist\Form;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_alchemist\Plugin\SectionStorage\ExoOverridesSectionStorage;
use Drupal\layout_builder\Form\OverridesEntityForm;

/**
 * Provides a form containing the Layout Builder UI for overrides.
 *
 * @internal
 *   Form classes are internal.
 */
class ExoOverridesEntityForm extends OverridesEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $section_storage = $this->sectionStorage;
    if ($section_storage instanceof ExoOverridesSectionStorage) {
      // We take our working entity and allow it to be saved. This allows
      // entity level changes to be stored.
      $this->entity = $section_storage->getEntity();
      // Entity references may have been stored for saving.
      if (!empty($this->entity->_exoComponentReferenceSave)) {
        foreach ($this->entity->_exoComponentReferenceSave as $referenced_entity) {
          if ($referenced_entity instanceof ContentEntityInterface) {
            // Save them.
            $referenced_entity->save();
          }
        }
      }
    }
    $return = parent::save($form, $form_state);
    return $return;
  }

}
