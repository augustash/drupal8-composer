<?php

namespace Drupal\exo_site_settings\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_site_settings\Event\SiteSettingsConfigCloneEvent;
use Drupal\field\FieldConfigInterface;

/**
 * Form controller for config page edit forms.
 *
 * @ingroup exo_site_settings
 */
class SiteSettingsForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\exo_site_settings\Entity\SiteSettings */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;
    foreach ($entity->getFieldDefinitions() as $field) {
      if ($field instanceof FieldConfigInterface) {
        $clone_info = $this->getCloneDefinition($field);
        if (!empty($clone_info['name']) && !empty($clone_info['key'])) {
          $config = $this->config($clone_info['name']);
          NestedArray::setValue($form, [
            $field->getName(),
            'widget',
            0,
            $field->getFieldStorageDefinition()->getMainPropertyName(),
            '#default_value',
          ], $config->get($clone_info['key']));
        }
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    foreach ($entity->getFieldDefinitions() as $field) {
      if ($field instanceof FieldConfigInterface) {
        $clone_info = $this->getCloneDefinition($field);
        if (!empty($clone_info['name']) && !empty($clone_info['key'])) {
          $value = $entity->get($field->getName())->{$field->getFieldStorageDefinition()->getMainPropertyName()};
          if ($field->getType() == 'link') {
            $value = str_replace('internal:', '', $value);
          }
          \Drupal::configFactory()->getEditable($clone_info['name'])
            ->set($clone_info['key'], $value)
            ->save();
        }
      }
    }

    switch ($status) {
      case SAVED_NEW:
        \Drupal::messenger()->addMessage($this->t('Created the %label config page.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        \Drupal::messenger()->addMessage($this->t('Saved the %label config page.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.exo_site_settings.collection');
  }

  /**
   * Get clone information.
   *
   * @param \Drupal\field\FieldConfigInterface $field
   *   The field config.
   *
   * @return array
   *   An array of name/key values.
   */
  protected function getCloneDefinition(FieldConfigInterface $field) {
    $name = $field->getThirdPartySetting('exo_site_settings', 'config_name');
    $key = $field->getThirdPartySetting('exo_site_settings', 'config_key');
    $event = new SiteSettingsConfigCloneEvent($field, $name, $key);
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch(SiteSettingsConfigCloneEvent::EVENT_NAME, $event);
    return [
      'name' => $event->getName(),
      'key' => $event->getKey(),
    ];
  }

}
