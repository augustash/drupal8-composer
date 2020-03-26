<?php

namespace Drupal\exo_entity_embed\Plugin\CKEditorPlugin;

use Drupal\entity_embed\Plugin\CKEditorPlugin\DrupalEntity;
use Drupal\editor\Entity\Editor;
use Drupal\embed\EmbedButtonInterface;
use Drupal\Component\Utility\Html;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * Defines the "exoentity" plugin.
 *
 * @CKEditorPlugin(
 *   id = "exoentity",
 *   label = @Translation("eXo Entity"),
 *   embed_type_id = "exo_entity"
 * )
 */
class ExoEntity extends DrupalEntity {
  use ExoIconTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'exo_entity_embed') . '/js/plugins/exoentity/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [
      'ExoEntity_dialogTitleAdd' => t('Insert entity'),
      'ExoEntity_dialogTitleEdit' => t('Edit entity'),
      'ExoEntity_buttons' => $this->getButtons(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getButton(EmbedButtonInterface $embed_button) {
    $button = function ($name, $icon, $direction = 'ltr') {
      $icon = $this->icon()->setIcon($icon);
      // In the markup below, we mostly use the name (which may include spaces),
      // but in one spot we use it as a CSS class, so strip spaces.
      // Note: this uses str_replace() instead of Html::cleanCssIdentifier()
      // because we must provide these class names exactly how CKEditor expects
      // them in its library, which cleanCssIdentifier() does not do.
      $class_name = str_replace(' ', '', $name);
      return [
        '#type' => 'inline_template',
        '#template' => '<a href="#" class="cke_{{ direction }}" role="button" title="{{ name }}" aria-label="{{ name }}">{{ icon }}</a>',
        '#context' => [
          'direction' => $direction,
          'name' => $name,
          'icon' => $icon->toMarkup(),
          'classname' => '_ exo-icon exo-icon-font icon-regular-window-close _',
        ],
      ];
    };
    return [
      'id' => $embed_button->id(),
      'name' => Html::escape($embed_button->label()),
      'label' => Html::escape($embed_button->label()),
      'image_alternative' => $button('bold', $embed_button->getTypeSetting('icon')),
      'image_alternative_rtl' => $button('bold', $embed_button->getTypeSetting('icon'), 'rtl'),
      'icon' => $embed_button->getTypeSetting('icon'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return array_merge(parent::getLibraries($editor), [
      'exo_entity_embed/embed',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCssFiles(Editor $editor) {
    return [
      drupal_get_path('module', 'system') . '/css/components/hidden.module.css',
      drupal_get_path('module', 'exo_entity_embed') . '/css/exo.entity-embed.css',
    ];
  }

}
