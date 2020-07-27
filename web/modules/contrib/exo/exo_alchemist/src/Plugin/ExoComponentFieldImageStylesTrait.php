<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\file\FileInterface;

/**
 * Class ExoComponentFieldImageStylesTrait.
 */
trait ExoComponentFieldImageStylesTrait {

  /**
   * {@inheritdoc}
   */
  protected function processDefinitionImageStyles(ExoComponentDefinitionField $field) {
    if ($styles = $field->getAdditionalValue('styles')) {
      $effect_manager = \Drupal::service('plugin.manager.image.effect');
      foreach ($styles as $key => $style_config) {
        if (empty($style_config['type'])) {
          throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [styles.%.type] be set.', $field->getType()));
        }
        $effect_type = 'image_' . $style_config['type'];
        if (!$effect_manager->hasDefinition($effect_type)) {
          throw new PluginException(sprintf('eXo Component Field plugin (%s) has requested an effect type [styles.%.type] that does not exist (%s).', $field->getType(), $effect_type));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function buildImageStyles(ExoComponentDefinitionField $field) {
    if ($styles = $field->getAdditionalValue('styles')) {
      $style_storage = \Drupal::entityTypeManager()->getStorage('image_style');
      $effect_manager = \Drupal::service('plugin.manager.image.effect');
      foreach ($styles as $key => $style_config) {
        $style_id = $this->imageStyleId($field, $key);
        $style_type = $style_config['type'];
        $style_prefix = 'image';
        // Suppor focal point.
        if (empty($style_config['prefix']) && in_array($style_type, ['crop', 'scale_and_crop']) && \Drupal::moduleHandler()->moduleExists('focal_point')) {
          $style_prefix = 'focal_point';
        }
        $effect_type = $style_prefix . '_' . $style_type;
        if ($effect_manager->hasDefinition($effect_type)) {
          $effect_configuration = $style_config;
          // All keys except type are config for image effect.
          unset($effect_configuration['type']);
          $style = $style_storage->load($style_id);
          /** @var \Drupal\image\ImageStyleInterface $style */
          if ($style) {
            foreach ($style->getEffects() as $effect) {
              $style->deleteImageEffect($effect);
            }
          }
          else {
            $style = $style_storage->create([
              'name' => $style_id,
            ]);
            /** @var \Drupal\image\ImageStyleInterface $style */
          }
          $style->set('label', 'Component: ' . $field->getComponent()->getLabel() . ' - ' . $field->getLabel() . ' (' . ucwords(str_replace('_', ' ', $key)) . ')');
          $effect = $effect_manager->createInstance($effect_type, [
            'uuid' => NULL,
            'id' => $effect_type,
            'weight' => 0,
            'data' => $effect_configuration,
          ]);
          $style->addImageEffect($effect->getConfiguration());
          $style->save();
          $field->addDependent($style->getConfigDependencyKey(), $style->getConfigDependencyName());
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getImageStylesAsUrl(ExoComponentDefinitionField $field, FileInterface $file) {
    $item_styles = [];
    if ($styles = $field->getAdditionalValue('styles')) {
      $style_storage = \Drupal::entityTypeManager()->getStorage('image_style');
      foreach ($styles as $key => $style_config) {
        $style_id = $this->imageStyleId($field, $key);
        $image_uri = $file->getFileUri();
        // Load image style "thumbnail".
        $style = $style_storage->load($style_id);
        /** @var \Drupal\image\ImageStyleInterface $style */
        // Get URL.
        if ($style) {
          $item_styles[$key] = $style->buildUrl($image_uri);
        }
      }
    }
    return $item_styles;
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfoImageStyles(ExoComponentDefinitionField $field) {
    $properties = [];
    if ($styles = $field->getAdditionalValue('styles')) {
      foreach ($styles as $key => $style_config) {
        $properties[$key] = $this->t('Url of the @style image style.', [
          '@style' => str_replace('_', ' ', $key),
        ]);
      }
    }
    return $properties;
  }

  /**
   * Given a field and a size key, return a unique size id.
   *
   * @return string
   *   An id.
   */
  protected function imageStyleId(ExoComponentDefinitionField $field, $style_key) {
    return 'exo_style_' . substr(hash('sha256', $field->id() . '_' . $style_key), 0, 22);
  }

}
