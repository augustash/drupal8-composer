<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Url;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionFieldPreview;

/**
 * Provides methods for creating file entities.
 */
trait ExoComponentFieldFileTrait {

  /**
   * {@inheritdoc}
   *
   * @param bool $required
   *   TRUE if path should be required.
   */
  protected function componentProcessDefinitionFile(ExoComponentDefinitionField $field, $required = FALSE) {
    if ($required && !$field->hasPreviewPropertyOnAll('path')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [preview.path] be set.', $field->getType()));
    }
    foreach ($field->getPreviews() as $preview) {
      // Support external and relative URLs.
      $uri = $preview->getValue('path');
      $uri_parts = parse_url($uri);
      if ($uri_parts === FALSE) {
        throw new PluginException(sprintf('eXo Component Field plugin (%s) has in invalid [preview.path]: (%s).', $field->getType(), $uri));
      }
      if (empty($uri_parts['scheme'])) {
        $uri = 'base://' . ltrim($field->getComponent()->getPath(), '/') . '/' . $uri;
      }
      $url = Url::fromUri($uri);
      if (!$url->isExternal()) {
        $path = \Drupal::service('app.root') . $url->toString();
        if (!file_exists($path)) {
          throw new PluginException(sprintf('eXo Component Field plugin (%s) has in invalid [preview.path]: (%s).', $field->getType(), $url->toString()));
        }
      }
      else {
        $path = $url->toString();
      }
      $preview->setValue('path', $path);
      // Validate file extention.
      if (!$preview->getValue('extension')) {
        $info = pathinfo($preview->getValue('path'));
        if (empty($info['extension'])) {
          throw new PluginException(sprintf('eXo Component Field plugin (%s) requires a valid file extension on [preview.path] or [preview.extension] be set.', $field->getType()));
        }
        $preview->setValue('extension', $info['extension']);
      }
    }
    $this->componentProcessDefinitionImageStyles($field);
  }

  /**
   * {@inheritdoc}
   */
  protected function componentFile(ExoComponentDefinitionFieldPreview $preview) {
    $file_data = file_get_contents($preview->getValue('path'));
    $file_uri = $this->getFileUri($preview);
    $file_directory = $this->getFileDirectory($preview);
    \Drupal::service('file_system')->prepareDirectory($file_directory, FileSystemInterface::CREATE_DIRECTORY);
    $file = file_save_data($file_data, $file_uri, FileSystemInterface::EXISTS_REPLACE);
    return $file;
  }

  /**
   * Get file uri.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionFieldPreview $preview
   *   The field preview.
   */
  protected function getFileUri(ExoComponentDefinitionFieldPreview $preview) {
    return $this->getFileDirectory($preview) . '/' . $this->getFileFilename($preview) . '.' . $this->getFileExtension($preview);
  }

  /**
   * Get file directory.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionFieldPreview $preview
   *   The field preview.
   */
  protected function getFileDirectory(ExoComponentDefinitionFieldPreview $preview) {
    $field = $preview->getField();
    return 'public://' . str_replace('_', '-', $field->getType() . '/' . $field->getName());
  }

  /**
   * Get file filename.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionFieldPreview $preview
   *   The field preview.
   */
  protected function getFileFilename(ExoComponentDefinitionFieldPreview $preview) {
    $field = $preview->getField();
    return str_replace(['_', '.'], '-', implode('_', [
      $field->getComponent()->id(),
      $field->getFieldName(),
      $preview->getDelta(),
      $field->getComponent()->getVersion(),
    ]));
  }

  /**
   * Get file extention.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionFieldPreview $preview
   *   The field preview.
   */
  protected function getFileExtension(ExoComponentDefinitionFieldPreview $preview) {
    return $preview->getValue('extension');
  }

}
