<?php

namespace Drupal\exo_alchemist\Command;

use Drupal\exo_alchemist\Definition\ExoComponentDefinition;
use Drupal\exo_alchemist\Plugin\ExoComponentPropertyOptionsInterface;

/**
 * Class ModifierTrait.
 *
 * @package Drupal\Console\Command
 */
trait ExoComponentModifierTrait {

  /**
   * The eXo component manager.
   *
   * @var \Drupal\exo_alchemist\ExoComponentManager
   */
  protected $exoComponentManager;

  /**
   * Get field questions.
   *
   * @return mixed
   *   Return an array or NULL.
   */
  public function modifierQuestion() {
    if ($this->getIo()->confirm(
      $this->trans('commands.exo.alchemist.component.questions.modifiers'),
      TRUE
    )) {
      $global_modifiers = ExoComponentDefinition::getGlobalModifiers();
      $modifiers = [];
      foreach ($global_modifiers as $global_modifier_id => $global_modifier) {
        $status = $this->getIo()->confirm(
          $this->trans(sprintf(
            $this->trans('commands.exo.alchemist.component.questions.modifier.enable'), $global_modifier['label']
          )),
          $global_modifier['status']
        );
        $modifiers['status'][$global_modifier_id] = $status ? 'TRUE' : 'FALSE';
        if ($status) {
          $instance = $this->exoComponentManager->getExoComponentPropertyManager()->createInstance($global_modifier['type']);
          if ($instance instanceof ExoComponentPropertyOptionsInterface) {
            $options = $instance->getOptions();
            unset($options['_none']);
            $options = array_keys($options);
            $this->getIo()->newLine();
            if (count($options) === 1 && reset($options) === 1) {
              $default = $this->getIo()->confirm(
                $this->trans(
                  $this->trans('commands.exo.alchemist.component.questions.modifier.default_bool')
                ),
                FALSE
              );
            }
            else {
              $this->getIo()->writeln(sprintf(
                $this->trans('commands.exo.alchemist.component.questions.modifier.options'), implode(', ', $options)
              ));
              $default = $this->getIo()->choiceNoList(
                $this->trans('commands.exo.alchemist.component.questions.modifier.default'),
                ['' => ''] + $options,
                '',
                TRUE
              );
            }
            if (!empty($default)) {
              if ($default == 1) {
                $default = TRUE;
              }
              $modifiers['defaults'][$global_modifier_id] = $default;
            }
          }
        }
      }
      return $modifiers;
    }
    return NULL;
  }

}
