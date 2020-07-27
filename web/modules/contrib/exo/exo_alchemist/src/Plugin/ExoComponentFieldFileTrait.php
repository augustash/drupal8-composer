<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Url;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\ExoComponentValue;

/**
 * Provides methods for creating file entities.
 */
trait ExoComponentFieldFileTrait {

  /**
   * {@inheritdoc}
   */
  public function validateValueFile(ExoComponentValue $value, $required = FALSE) {
    $field = $value->getDefinition();
    if ($required && !$value->has('path')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [default.path] be set.', $field->getType()));
    }
    // Support external and relative URLs.
    $uri = $value->get('path');
    $uri_parts = parse_url($uri);
    if ($uri_parts === FALSE) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) has in invalid [default.path]: (%s).', $field->getType(), $uri));
    }
    if (file_exists($uri)) {
      // File exists. Use it.
      $uri = 'base://' . $uri;
    }
    elseif (empty($uri_parts['scheme'])) {
      $uri = 'base://' . ltrim($field->getComponent()->getPath(), '/') . '/' . $uri;
    }
    $url = Url::fromUri($uri);
    if (!$url->isExternal()) {
      $path = \Drupal::service('app.root') . $url->toString();
      if (!file_exists($path)) {
        throw new PluginException(sprintf('eXo Component Field plugin (%s) has in invalid [default.path]: (%s).', $field->getType(), $url->toString()));
      }
    }
    else {
      $path = $url->toString();
    }
    $value->set('path', $path);
    // Validate file extention.
    if (!$value->get('extension')) {
      $info = pathinfo($value->get('path'));
      if (empty($info['extension'])) {
        throw new PluginException(sprintf('eXo Component Field plugin (%s) requires a valid file extension on [default.path] or [default.extension] be set.', $field->getType()));
      }
      $value->set('extension', $info['extension']);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function componentFile(ExoComponentValue $value) {
    $file_data = file_get_contents($value->get('path'));
    $file_uri = $this->getFileUri($value);
    $file_directory = $this->getFileDirectory($value);
    \Drupal::service('file_system')->prepareDirectory($file_directory, FileSystemInterface::CREATE_DIRECTORY);
    $file = file_save_data($file_data, $file_uri, FileSystemInterface::EXISTS_REPLACE);
    return $file;
  }

  /**
   * Get file uri.
   *
   * @param \Drupal\exo_alchemist\ExoComponentValue $value
   *   The field default.
   */
  protected function getFileUri(ExoComponentValue $value) {
    return $this->getFileDirectory($value) . '/' . $this->getFileFilename($value) . '.' . $this->getFileExtension($value);
  }

  /**
   * Get file directory.
   *
   * @param \Drupal\exo_alchemist\ExoComponentValue $value
   *   The field default.
   */
  protected function getFileDirectory(ExoComponentValue $value) {
    $field = $value->getDefinition();
    return 'public://' . str_replace('_', '-', $field->getType() . '/' . $field->getName());
  }

  /**
   * Get file filename.
   *
   * @param \Drupal\exo_alchemist\ExoComponentValue $value
   *   The field default.
   */
  protected function getFileFilename(ExoComponentValue $value) {
    $filename = $value->get('filename');
    if (empty($filename)) {
      $field = $value->getDefinition();
      $key = [
        $field->getComponent()->id(),
        $field->getFieldName(),
        $value->getDelta(),
        $field->getComponent()->getVersion(),
        (string) $field->getComponent()->getAdditionalValue('_delta'),
      ];
      $filename = md5(implode('_', $key));
    }
    return str_replace(['_', '.', ' '], '-', strtolower($filename)) . '-' . time();
  }

  /**
   * Get file extention.
   *
   * @param \Drupal\exo_alchemist\ExoComponentValue $value
   *   The field default.
   */
  protected function getFileExtension(ExoComponentValue $value) {
    return $value->get('extension');
  }

}
