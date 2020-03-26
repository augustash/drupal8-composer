<?php

namespace Drupal\exo_config_file\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\TypedData\TranslatableInterface;

/**
 * Defines the eXo Config File entity.
 *
 * @ConfigEntityType(
 *   id = "exo_config_file",
 *   label = @Translation("eXo Config File"),
 *   handlers = {
 *     "list_builder" = "Drupal\exo_config_file\ExoConfigFileListBuilder",
 *     "form" = {
 *       "delete" = "Drupal\exo_config_file\Form\ExoConfigFileDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\exo_config_file\ExoConfigFileHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "exo_config_file",
 *   admin_permission = "administer exo config file",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "delete-form" = "/admin/config/exo/config-files/{exo_config_file}/delete",
 *     "collection" = "/admin/config/exo/config-files"
 *   }
 * )
 */
class ExoConfigFile extends ConfigEntityBase implements ExoConfigFileInterface {

  /**
   * The eXo Config File ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The file name.
   *
   * @var string
   */
  protected $name;

  /**
   * The file directory.
   *
   * @var string
   */
  protected $directory;

  /**
   * The file extension.
   *
   * @var string
   */
  protected $extension;

  /**
   * The base64 encoded file broken into an array of parts.
   *
   * @var array
   */
  protected $file;

  /**
   * The parent dependency.
   *
   * @var array
   */
  protected $dependency;

  /**
   * {@inheritdoc}
   */
  public function getParentEntityType() {
    return $this->get('parent_type');
  }

  /**
   * {@inheritdoc}
   */
  public function getParentEntityId() {
    return $this->get('parent_id');
  }

  /**
   * {@inheritdoc}
   */
  public function getParentEntityField() {
    return $this->get('parent_field');
  }

  /**
   * {@inheritdoc}
   */
  public function getParentEntity() {
    $parent_type = $this->getParentEntityType();
    $parent_id = $this->getParentEntityId();
    if (!$parent_type || !$parent_id) {
      return NULL;
    }
    $parent = \Drupal::entityTypeManager()->getStorage($parent_type)->load($parent_id);
    // Return current translation of parent entity, if it exists.
    if ($parent != NULL && ($parent instanceof TranslatableInterface) && $parent->hasTranslation($this->language()->getId())) {
      return $parent->getTranslation($this->language()->getId());
    }
    return $parent;
  }

  /**
   * {@inheritdoc}
   */
  public function getFileName() {
    return $this->get('name');
  }

  /**
   * {@inheritdoc}
   */
  public function getFileDirectory() {
    return $this->get('directory');
  }

  /**
   * {@inheritdoc}
   */
  public function getFilePath() {
    $path = $this->getFileDirectory() . '/' . $this->getFileName();
    if (!$this->isArchive()) {
      $path .= '.' . $this->getFileExtension();
    }
    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function getFileExtension() {
    return $this->get('extension');
  }

  /**
   * {@inheritdoc}
   */
  public function getDependency() {
    if ($entity = $this->getParentEntity()) {
      return $entity->getConfigDependencyName();
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function isArchive() {
    return $this->getFileExtension() == 'zip';
  }

  /**
   * {@inheritdoc}
   */
  public function isImage() {
    return in_array($this->getFileExtension(), [
      'jpg',
      'jpeg',
      'png',
      'gif',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getEncodedFile() {
    return $this->get('file');
  }

  /**
   * {@inheritdoc}
   */
  public function setFile($path) {
    $data = strtr(base64_encode(addslashes(gzcompress(serialize(file_get_contents($path)), 9))), '+/=', '-_,');
    $parts = str_split($data, 200000);
    $this->set('file', $parts);

    $extension = pathinfo($path, PATHINFO_EXTENSION);
    $this->set('extension', $extension);
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    $data = implode('', $this->getEncodedFile());
    return unserialize(gzuncompress(stripslashes(base64_decode(strtr($data, '-_,', '+/=')))));
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    if (!$this->isNew()) {
      $original = $storage->loadUnchanged($this->getOriginalId());
    }

    foreach ([
      'parent_type',
      'parent_id',
      'parent_field',
      'name',
      'directory',
      'extension',
      'file',
    ] as $field_id) {
      if (!$this->get($field_id)) {
        throw new EntityMalformedException(t('File @field_id is required.', ['@field_id' => $field_id]));
      }
    }

    if ($this->isNew() || $original->getEncodedFile() !== $this->getEncodedFile()) {
      static::deleteFile($storage, $this);
      $this->saveFile();
      // Because these entities are built as configuration, the parent entity
      // may have been built before the files were actually created. We resave
      // the parent to give it a change to act on the files using the
      // exoConfigFileUpload flag.
      $parent = $this->getParentEntity();
      if ($parent && method_exists($parent, 'exoConfigFileUpdate')) {
        $parent->{'exoConfigFileUpdate'}($this);
      }
    }
  }

  /**
   * Take base64 encoded file data and convert it to a file.
   */
  protected function saveFile() {
    try {
      if ($this->isArchive()) {
        $this->saveArchive();
      }
      else {
        $this->saveBasic();
        if ($this->isImage()) {
          image_path_flush($this->getFilePath());
        }
      }
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addMessage($e->getMessage(), 'error');
    }
  }

  /**
   * Store a standard file.
   */
  public function saveBasic() {
    $data = $this->getFile();
    $directory = $this->getFileDirectory();
    $path = $directory . '/' . $this->getFileName() . '.' . $this->getFileExtension();
    $file_system = \Drupal::service('file_system');
    $file_system->prepareDirectory($directory, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
    file_put_contents($path, $data);
  }

  /**
   * Properly extract and store a zip file.
   */
  public function saveArchive() {
    $data = $this->getFile();
    $tmp_path = 'temporary://' . $this->getFileName() . '.' . $this->getFileExtension();
    file_put_contents($tmp_path, $data);

    $archiver = archiver_get_archiver($tmp_path);
    if (!$archiver) {
      throw new Exception(t('Cannot extract %file, not a valid archive.', ['%file' => $tmp_path]));
    }

    $directory = $this->getFileDirectory() . '/' . $this->getFileName();
    $file_system = \Drupal::service('file_system');
    $file_system->prepareDirectory($directory, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
    $archiver->extract($directory);
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);
    foreach ($entities as $entity) {
      static::deleteFile($storage, $entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function deleteFile(EntityStorageInterface $storage, ExoConfigFileInterface $entity) {
    if (!$entity->isNew()) {
      $original = $storage->loadUnchanged($entity->getOriginalId());
      $directory = $original->getFileDirectory();
      $path = $directory . '/' . $original->getFileName() . '.' . $original->getFileExtension();
      $file_system = \Drupal::service('file_system');
      $file_system->delete($path);
      $archive_directory = $directory . '/' . $original->getFileName();
      $file_system->deleteRecursive($archive_directory);
      // Clean up empty directory. Will fail silently if it is not empty.
      @rmdir($directory);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();
    if ($dependency = $this->getDependency()) {
      $this->addDependency('config', $dependency);
    }
    return $this;
  }

}
