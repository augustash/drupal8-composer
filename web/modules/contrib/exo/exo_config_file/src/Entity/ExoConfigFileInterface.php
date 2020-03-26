<?php

namespace Drupal\exo_config_file\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Provides an interface for defining eXo Config File entities.
 */
interface ExoConfigFileInterface extends ConfigEntityInterface {

  /**
   * Get the parent entity type.
   *
   * @return string
   *   The parent entity type id.
   */
  public function getParentEntityType();

  /**
   * Get the parent entity id.
   *
   * @return string
   *   The parent entity id.
   */
  public function getParentEntityId();

  /**
   * Get the parent entity field name.
   *
   * @return string
   *   The parent entity field name.
   */
  public function getParentEntityField();

  /**
   * Get the parent entity.
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityInterface
   *   The parent entity.
   */
  public function getParentEntity();

  /**
   * Get the file or folder name.
   *
   * @return string
   *   The file or folder name.
   */
  public function getFileName();

  /**
   * Get the file directory.
   *
   * @return string
   *   The file directory.
   */
  public function getFileDirectory();

  /**
   * Get the full file path.
   *
   * @return string
   *   The full file path.
   */
  public function getFilePath();

  /**
   * Get the file extension.
   *
   * @return string
   *   The file extension.
   */
  public function getFileExtension();

  /**
   * Get the dependency.
   *
   * @return string
   *   The dependency.
   */
  public function getDependency();

  /**
   * Check if this is an archive.
   *
   * @return bool
   *   Will return TRUE if file is an archive.
   */
  public function isArchive();

  /**
   * Check if this is an image.
   *
   * @return bool
   *   Will return TRUE if file is an image.
   */
  public function isImage();

  /**
   * Get the base64 encoded string.
   *
   * @return array
   *   The array of base64 encoded strings.
   */
  public function getEncodedFile();

  /**
   * Set the file as base64 encoded string.
   *
   * @param string $path
   *   The path to the file to encode into the entity.
   */
  public function setFile($path);

  /**
   * Get the file from base64 encoded string.
   *
   * @return string
   *   The deserialized file.
   */
  public function getFile();

  /**
   * Properly clean up old files. Could be a folder or a file.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage object.
   * @param \Drupal\exo_config_file\Entity\ExoConfigFileInterface $entity
   *   The eXo config file entity.
   */
  public static function deleteFile(EntityStorageInterface $storage, ExoConfigFileInterface $entity);

}
