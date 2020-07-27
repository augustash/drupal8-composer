<?php

namespace Drupal\exo_alchemist\Command;

use Drupal\Core\Plugin\PluginBase;

/**
 * Class FormTrait.
 *
 * @package Drupal\Console\Command
 */
trait ExoComponentFieldTrait {

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
  public function fieldQuestion() {
    if ($this->getIo()->confirm(
      $this->trans('commands.exo.alchemist.component.questions.fields'),
      TRUE
    )) {
      $field_types = [];
      $field_types_of_field = [];
      foreach ($this->exoComponentManager->getExoComponentFieldManager()->getDefinitions() as $id => $definition) {
        if (empty($definition['hidden'])) {
          $field_types[$definition['id']] = $definition['id'];
          $field_types_of_field[$definition['id']][] = substr($id, strlen($definition['id']) + 1);
        }
      }
      sort($field_types);

      $this->getIo()->writeln(sprintf(
        $this->trans('commands.common.messages.available-field-types'), implode(', ', $field_types)
      ));
      $this->getIo()->newLine();

      $fields = [];
      while (TRUE) {
        $this->getIo()->comment($this->trans('commands.common.questions.inputs.new-field'));
        $this->getIo()->newLine();
        $field_type = $original_field_type = $this->getIo()->choiceNoList(
          $this->trans('commands.common.questions.inputs.type'),
          $field_types,
          '',
          TRUE
        );

        if (empty($field_type) || is_numeric($field_type)) {
          break;
        }

        switch ($original_field_type) {
          case 'field':
          case 'extra_field':
          case 'display':
          case 'display_component':
          case 'reference_display':
            $field_type .= PluginBase::DERIVATIVE_SEPARATOR . $this->getIo()->choiceNoList(
              $this->trans('commands.exo.alchemist.component.questions.field.field'),
              $field_types_of_field[$original_field_type],
              '',
              TRUE
            );
            break;
        }

        $field_label = $this->getIo()->ask(
          $this->trans('commands.exo.alchemist.component.questions.field.label'),
          NULL
        );

        // Machine name.
        $field_id = $this->getIo()->ask(
          $this->trans('commands.exo.alchemist.component.questions.field.id'),
          $this->stringConverter->createMachineName($field_label)
        );

        $field_description = $this->getIo()->askEmpty(
          $this->trans('commands.exo.alchemist.component.questions.field.description')
        );

        $field_default = $this->getIo()->askEmpty(
          $this->trans('commands.exo.alchemist.component.questions.field.default'),
          NULL
        );

        $field_sample = NULL;
        if ($original_field_type === 'field') {
          $field_sample = $this->getIo()->askEmpty(
            $this->trans('commands.exo.alchemist.component.questions.field.sample'),
            NULL
          );
        }

        array_push($fields, [
          'id' => $field_id,
          'type' => $field_type,
          'label' => $field_label,
          'description' => $field_description,
          'default' => $field_default,
          'sample' => $field_sample,
        ]);

        $this->getIo()->newLine();
      }
      return $fields;
    }
    return NULL;
  }

}
