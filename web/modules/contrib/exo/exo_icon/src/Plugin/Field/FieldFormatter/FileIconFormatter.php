<?php

namespace Drupal\exo_icon\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * Plugin implementation of the 'file_icon' formatter.
 *
 * @FieldFormatter(
 *   id = "file_icon",
 *   label = @Translation("eXo Icon"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class FileIconFormatter extends FileFormatterBase {
  use ExoIconTranslationTrait;

  /**
   * The mime manager.
   *
   * @var \Drupal\exo_icon\ExoIconMimeManager
   */
  protected $mimeManager;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = [
      'title' => '',
      'icon' => 'regular-file',
      'position' => 'before',
      'target' => '',
      'text_only' => '',
    ];
    return $settings + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = t('Link title as @title', ['@title' => $this->getSetting('title') ? $this->getSetting('title') : 'Default']);
    if ($position = $this->getSetting('position')) {
      $summary[] = t('Icon position: @value', ['@value' => ucfirst($position)]);
    }
    if ($this->getSetting('text_only')) {
      $summary[] = t('Text only');
    }
    else {
      if ($this->getSetting('target')) {
        $summary[] = t('Open link in new window');
      }
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link title'),
      '#description' => $this->t('If left empty, the file description will be used.'),
      '#default_value' => $this->getSetting('title'),
    ];

    $can_change_icon = \Drupal::currentUser()->hasPermission('administer exo icon');

    $elements['text_only'] = [
      '#type' => 'checkbox',
      '#title' => t('Text only'),
      '#default_value' => $this->getSetting('text_only'),
      '#access' => $can_change_icon,
    ];

    $elements['target'] = [
      '#type' => 'checkbox',
      '#title' => t('Open link in new window'),
      '#return_value' => '_blank',
      '#default_value' => $this->getSetting('target'),
      '#states' => [
        'invisible' => [
          ':input[name*="text_only"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $elements['position'] = [
      '#type' => 'select',
      '#title' => $this->t('Icon position'),
      '#options' => ['before' => $this->t('Before'), 'after' => $this->t('After')],
      '#default_value' => $this->getSetting('position'),
      '#required' => TRUE,
      '#access' => $can_change_icon,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      $item = $file->_referringItem;
      $url = file_create_url($file->getFileUri());
      $options = [];
      if ($this->getSetting('target')) {
        $options['attributes']['target'] = '_blank';
      }

      $link_text = !empty($this->getSetting('title')) ? $this->getSetting('title') : $item->description;
      if (empty($link_text)) {
        $link_text = $item->getEntity()->label();
      }
      $position = $this->getSetting('position');
      $link_text = $this->icon($link_text)->setIcon($this->mimeManager()->getMimeIcon($file->getMimeType()));
      if ($position == 'after') {
        $link_text->setIconAfter();
      }
      if ($this->getSetting('text_only')) {
        $elements[$delta]['#markup'] = $link_text;
      }
      else {
        $elements[$delta] = Link::fromTextAndUrl($link_text, Url::fromUri($url, $options))->toRenderable();
      }
      $elements[$delta]['#cache']['tags'] = $file->getCacheTags();
      // Pass field item attributes to the theme function.
      if (isset($item->_attributes)) {
        $elements[$delta] += ['#attributes' => []];
        $elements[$delta]['#attributes'] += $item->_attributes;
        // Unset field item attributes since they have been included in the
        // formatter output and should not be rendered in the field template.
        unset($item->_attributes);
      }
    }

    return $elements;
  }

  /**
   * Returns the eXo icon mime manager.
   *
   * @return \Drupal\exo_icon\ExoIconMimeManager
   *   The mime manager.
   */
  protected function mimeManager() {
    if (!$this->mimeManager) {
      $this->mimeManager = \Drupal::service('exo_icon.mime_manager');
    }
    return $this->mimeManager;
  }

}
