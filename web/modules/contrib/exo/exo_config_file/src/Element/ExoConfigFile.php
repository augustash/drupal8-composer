<?php

namespace Drupal\exo_config_file\Element;

use Drupal\Core\Render\Element\File;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Bytes;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\file\FileInterface;
use Drupal\Core\Entity\EntityMalformedException;

/**
 * Provides a form element for uploading a config file.
 *
 * If you add this element to a form the enctype="multipart/form-data" attribute
 * will automatically be added to the form element.
 *
 * Properties:
 * - #multiple: A Boolean indicating whether multiple files may be uploaded.
 * - #size: The size of the file input element in characters.
 *
 * @FormElement("exo_config_file")
 */
class ExoConfigFile extends File {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return parent::getInfo() + [
      '#extensions' => ['txt'],
      '#required' => FALSE,
      '#file_destination' => 'public://[exo_config_file:type]',
      '#file_name' => '[exo_config_file:id]-[exo_config_file:field_name]',
      '#upload_validators' => [],
    ];
  }

  /**
   * Checks if config file form is valid.
   *
   * @param array $form
   *   The form definition array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return bool
   *   Return TRUE if valid.
   */
  public static function formIsValid(array $form, FormStateInterface $form_state) {
    $complete_form = $form_state->getFormObject();
    return $complete_form instanceof EntityFormInterface;
  }

  /**
   * Processes a 'exo_config_file' upload element.
   */
  public static function processFile(&$element, FormStateInterface $form_state, &$complete_form) {
    if (!static::formIsValid($complete_form, $form_state)) {
      $element['#disabled'] = TRUE;
      return $element;
    }

    $element = parent::processFile($element, $form_state, $complete_form);
    $field_name = end($element['#parents']);
    $config_file_field_name = '_' . $field_name;

    // Add global validation handler.
    $validate_handler_exists = array_filter($complete_form['#validate'], function ($validate) {
      return is_array($validate) && isset($validate[1]) && $validate[1] === 'exoConfigFilesValidate';
    });
    if (!$validate_handler_exists) {
      array_unshift($complete_form['#validate'], [static::class, 'exoConfigFilesValidate']);
    }

    // Add global submit handler.
    $submit_handler_exists = array_filter($complete_form['actions']['submit']['#submit'], function ($submit) {
      return is_array($submit) && isset($submit[1]) && $submit[1] === 'exoConfigFilesSubmit';
    });
    if (!$submit_handler_exists) {
      array_unshift($complete_form['actions']['submit']['#submit'], [static::class, 'exoConfigFilesSubmit']);
    }

    // Store the field names for later processing.
    $config_file_field_names = $form_state->get('exo_config_file_field_names');
    if (empty($config_file_field_names)) {
      $config_file_field_names = [];
    }
    $config_file_field_names[$field_name] = [
      'name' => $field_name,
      'config_file_field_name' => $config_file_field_name,
      'parents' => isset($element['#final_parents']) ? $element['#final_parents'] : $element['#parents'],
      'array_parents' => $element['#array_parents'],
    ];
    $form_state->set('exo_config_file_field_names', $config_file_field_names);

    // Setup validators.
    $validators = [
      'file_validate_extensions' => [implode(' ', $element['#extensions'])],
      'file_validate_size' => [Bytes::toInt('10MB')],
    ] + $element['#upload_validators'];
    $element['#upload_validators'] = $validators;
    $description = isset($element['#description']) ? $element['#description'] : '';
    $element['#description'] = [
      '#theme' => 'file_upload_help',
      '#upload_validators' => $validators,
      '#description' => $description,
    ];

    // Handle requried fields manually.
    // @see preRenderFile
    $element['#exo_config_file_required'] = $element['#required'];
    $element['#required'] = FALSE;

    // Rename #name to avoid data collisions.
    $element['#name'] = 'files[' . $config_file_field_name . ']';
    $element['#original_name'] = $field_name;
    $element['#exo_config_file_field_name'] = $config_file_field_name;
    $form_state->setHasFileElement();
    return $element;
  }

  /**
   * Prepares a #type 'exo_config_file' render element for input.html.twig.
   *
   * @param mixed $element
   *   An associative array containing the properties of the element.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  public static function preRenderFile($element) {
    $element = parent::preRenderFile($element);
    if (isset($element['#exo_config_file_required'])) {
      $element['#required'] = $element['#exo_config_file_required'];
    }
    return $element;
  }

  /**
   * Form validation handler for #type 'exo_config_file'.
   *
   * This will only be called a single time on a form no matter how many
   * 'exo_config_file' fields are on the form.
   *
   * @param array $form
   *   The form definition array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function exoConfigFilesValidate(array $form, FormStateInterface $form_state) {
    $all_files = \Drupal::request()->files->get('files', []);
    foreach ($form_state->get('exo_config_file_field_names') as $data) {
      $element = NestedArray::getValue($form, $data['array_parents']);
      $config_file_field_name = $data['config_file_field_name'];
      // Make sure there's an upload to process.
      if (empty($all_files[$config_file_field_name])) {
        if ($element['#exo_config_file_required']) {
          $form_state->setError($element, t('@title is required.', ['@title' => $element['#title']]));
        }
        continue;
      }
      $file = file_save_upload($config_file_field_name, $element['#upload_validators'], FALSE, 0);
      if ($file) {
        $form_state->set('exo_config_file_' . $config_file_field_name, $file);
      }
      else {
        $form_state->setError($element, t('Unable to upload file.'));
      }
    }
  }

  /**
   * Form submit handler for #type 'exo_config_file'.
   *
   * This will only be called a single time on a form no matter how many
   * 'exo_config_file' fields are on the form.
   *
   * @param array $form
   *   The form definition array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function exoConfigFilesSubmit(array $form, FormStateInterface $form_state) {
    foreach ($form_state->get('exo_config_file_field_names') as $data) {
      $file = $form_state->get('exo_config_file_' . $data['config_file_field_name']);
      if ($file) {
        try {
          $element = NestedArray::getValue($form, $data['array_parents']);
          /* @var \Drupal\exo_config_file\Entity\ExoConfigFileInterface $exo_config_file */
          $exo_config_file = static::exoConfigFileSubmit($file, $element, $form_state);
          $form_state->setValue($data['parents'], $exo_config_file->getFilePath());
        }
        catch (\Exception $e) {
          \Drupal::messenger()->addMessage($e->getMessage(), 'error');
          return;
        }
      }
    }
  }

  /**
   * Handle the saving of an Exo Config File.
   *
   * @param \Drupal\file\FileInterface $file
   *   A temporary file entity.
   * @param array $element
   *   An 'exo_config_file' field definition array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\exo_config_file\Entity\ExoConfigFileInterface
   *   The saved/updated Exo Config File entity.
   */
  public static function exoConfigFileSubmit(FileInterface $file, array $element, FormStateInterface $form_state) {
    $token = \Drupal::service('token');
    $complete_form = $form_state->getFormObject();
    /* @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $complete_form->getEntity();
    if ($entity->isNew()) {
      // We save to make sure have an ID.
      try {
        $entity->save();
        $entity_id = $entity->id();
      }
      catch (EntityMalformedException $e) {
        // Some entities like blocks will still not have an id at this stage.
        // However, sometimes we can access it via the form state.
        $entity_id = $form_state->getValue('id');
        if ($entity_id) {
          $entity->set('id', $entity_id)->save();
        }
        else {
          throw($e);
        }
      }
    }
    $field_name = $element['#original_name'];
    $token_data = [
      $entity->getEntityTypeId() => $entity,
      'exo_config_file' => [
        'type' => $entity->getEntityTypeId(),
        'id' => $entity->id(),
        'field_name' => $field_name,
      ],
    ];

    $exo_config_file_id = static::getConfigFileEntityId($entity, $field_name);
    $storage = \Drupal::entityTypeManager()->getStorage('exo_config_file');
    /* @var \Drupal\exo_file_config\Entity\ExoConfigFileInterface $exo_config_file */
    $exo_config_file = $storage->load($exo_config_file_id);
    if (!$exo_config_file) {
      $exo_config_file = $storage->create([
        'id' => $exo_config_file_id,
        'parent_type' => $entity->getEntityTypeId(),
        'parent_id' => $entity->id(),
        'parent_field' => $field_name,
        'directory' => str_replace('_', '-', $token->replace($element['#file_destination'], $token_data)),
        'name' => str_replace('_', '-', $token->replace($element['#file_name'], $token_data)),
      ]);
    }
    $exo_config_file->setFile($file->getFileUri());
    $exo_config_file->save();
    return $exo_config_file;
  }

  /**
   * The ID of the eXo config file entity that will be generated.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity this plugin belongs to.
   * @param string $field_name
   *   The unique field id of the file.
   *
   * @return string
   *   The ID of the config file entity.
   */
  public static function getConfigFileEntityId(EntityInterface $entity, $field_name) {
    return implode('_', [
      $entity->getEntityTypeId(),
      $entity->id(),
      $field_name,
    ]);
  }

}
