<?php

namespace Drupal\exo_alchemist\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\PluginFormFactoryInterface;
use Drupal\Core\Render\Element;
use Drupal\exo_alchemist\Ajax\ExoComponentModifierAttributesCommand;
use Drupal\exo_alchemist\Controller\ExoFieldParentsTrait;
use Drupal\exo_alchemist\ExoComponentManager;
use Drupal\exo_alchemist\ExoComponentPropertyManager;
use Drupal\layout_builder\Form\ConfigureBlockFormBase;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\SectionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to update a block.
 *
 * @internal
 *   Form classes are internal.
 */
class ExoFieldUpdateForm extends ConfigureBlockFormBase {

  use ExoFieldParentsTrait;

  /**
   * The eXo component plugin manager.
   *
   * @var \Drupal\exo_alchemist\ExoComponentManager
   */
  protected $exoComponentManager;

  /**
   * The plugin being configured.
   *
   * @var \Drupal\layout_builder\Plugin\Block\InlineBlock
   */
  protected $block;

  /**
   * The parent entity.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $inlineBlock;

  /**
   * The active entity.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entity;

  /**
   * The active entity parents.
   *
   * @var array
   */
  protected $parents;

  /**
   * Constructs a new block form.
   *
   * @param \Drupal\layout_builder\LayoutTempstoreRepositoryInterface $layout_tempstore_repository
   *   The layout tempstore repository.
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $context_repository
   *   The context repository.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   *   The UUID generator.
   * @param \Drupal\Core\Plugin\PluginFormFactoryInterface $plugin_form_manager
   *   The plugin form manager.
   * @param \Drupal\exo_alchemist\ExoComponentManager $exo_component_manager
   *   The eXo component manager.
   */
  public function __construct(LayoutTempstoreRepositoryInterface $layout_tempstore_repository, ContextRepositoryInterface $context_repository, BlockManagerInterface $block_manager, UuidInterface $uuid, PluginFormFactoryInterface $plugin_form_manager, ExoComponentManager $exo_component_manager) {
    $this->layoutTempstoreRepository = $layout_tempstore_repository;
    $this->contextRepository = $context_repository;
    $this->blockManager = $block_manager;
    $this->uuidGenerator = $uuid;
    $this->pluginFormFactory = $plugin_form_manager;
    $this->exoComponentManager = $exo_component_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('layout_builder.tempstore_repository'),
      $container->get('context.repository'),
      $container->get('plugin.manager.block'),
      $container->get('uuid'),
      $container->get('plugin_form.factory'),
      $container->get('plugin.manager.exo_component')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_builder_component_update_field';
  }

  /**
   * Builds the block form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage being configured.
   * @param int $delta
   *   The delta of the section.
   * @param string $region
   *   The region of the block.
   * @param string $uuid
   *   The UUID of the block being updated.
   * @param string $path
   *   The path to the field requested for updating.
   *
   * @return array
   *   The form array.
   */
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL, $delta = NULL, $region = NULL, $uuid = NULL, $path = NULL) {
    $this->sectionStorage = $section_storage;
    $component = $section_storage->getSection($delta)->getComponent($uuid);
    /** @var Drupal\layout_builder\Plugin\Block\InlineBlock $this->block */
    $this->block = $component->getPlugin();

    $form = $this->doBuildForm($form, $form_state, $section_storage, $delta, $component);
    $this->inlineBlock = $form_state->getTemporaryValue('inlineBlock') ?: $form['settings']['block_form']['#block'];

    $parents = explode('.', $path);
    $entity = $this->getTargetEntity($this->inlineBlock, $parents);
    $inline_block_definition = $this->exoComponentManager->getEntityBundleComponentDefinition($this->inlineBlock->type->entity);

    // We do not edit media entities from with Alchemist. We always want their
    // parent field.
    // @todo Come up with a way to do this from the field plugin.
    if ($entity->getEntityTypeId() == 'media') {
      array_pop($parents);
      $entity = $this->getTargetEntity($this->inlineBlock, $parents);
    }
    $this->parents = $parents;
    $this->entity = $entity;

    $field_name = end($parents);
    $delta = NULL;
    $definition = $this->exoComponentManager->getEntityBundleComponentDefinition($entity->type->entity);

    $modifier_target = NULL;
    if (is_numeric($field_name)) {
      // We are targeting the delta of a field.
      $delta = $field_name;
      $field_name = prev($parents);
      $modifier_target = $definition->getModifierTarget();
    }

    // We are targeting a field.
    if ($field = $definition->getFieldBySafeId($field_name)) {
      $modifier_target = $field->getModifierTarget();
    }

    // Modifier support.
    $field_reset = [];
    if ($modifier_target && ($field_modifier = $inline_block_definition->getModifier($modifier_target))) {
      if ($field_modifier) {
        $this->exoComponentManager->getExoComponentPropertyManager()->buildForm($form, $form_state, $inline_block_definition, $this->inlineBlock);
        if (!empty($form['modifiers'])) {
          $form['modifiers']['#attributes']['data-exo-alchemist-revert'] = TRUE;
          foreach ($inline_block_definition->getModifiers() as $modifier) {
            if ($field_modifier->getName() === $modifier->getName()) {
              $form['modifiers'][$modifier->getName()]['#title'] = $this->t('Appearance');
              $form['modifiers'][$modifier->getName()]['#type'] = 'fieldset';
              $form['modifiers'][$modifier->getName()]['#group'] = '';
            }
            if ($field_modifier->getName() !== $modifier->getName() && $field_modifier->getName() !== $modifier->getGroup()) {
              unset($form['modifiers'][$modifier->getName()]);
            }
            else {
              $field_reset[] = $modifier->getName();
            }
          }
        }

        $form['modifiers']['_info'] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => [
              'exo-alchemist-revert-message',
              'messages',
              'messages--warning',
              'warning',
              'hidden',
            ],
          ],
          '#children' => $this->t('You have unsaved changes.'),
          '#weight' => -10,
        ];

        $form['refresh'] = [
          '#type' => 'submit',
          '#value' => $this->t('Refresh'),
          '#id' => 'exo-alchemist-appearance-refresh',
          '#attributes' => [
            'class' => ['hidden'],
          ],
          '#ajax' => [
            'callback' => '::ajaxAppearanceSubmit',
            'progress' => [
              'type' => 'none',
            ],
          ],
        ];
      }
    }

    // Determine the fields that should be visible.
    $allowed_fields = [$field_name];
    foreach ($definition->getFields() as $field) {
      $field_definition = $definition->getFieldBySafeId($field_name);
      if ($field_definition && $field->getGroup() == $field_definition->getName()) {
        $allowed_fields[] = $field->getFieldName();
      }
    }

    $form['settings']['admin_label']['#access'] = FALSE;
    $form['settings']['label']['#access'] = FALSE;
    $form['settings']['label_display']['#access'] = FALSE;
    $form['settings']['block_form']['#block'] = $entity;
    $form['settings']['block_form']['#process'][] = '::processBlockForm';
    $form['settings']['block_form']['#allowed_fields'] = $allowed_fields;
    $form['settings']['block_form']['#field_name'] = $field_name;
    $form['settings']['block_form']['#delta'] = $delta;
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit']['#do_submit'] = TRUE;
    if (!empty($field_reset)) {
      $form['actions']['reset'] = [
        '#type' => 'submit',
        '#value' => $this->t('Reset Appearance'),
        '#op' => 'reset',
        '#field_reset' => $field_reset,
        '#do_submit' => TRUE,
        '#ajax' => [
          'callback' => '::ajaxSubmit',
        ],
      ];
    }
    $form['actions']['#weight'] = 100;
    $form['#exo_theme'] = 'black';

    return $form;
  }

  /**
   * Process callback to insert a Custom Block form.
   *
   * @param array $element
   *   The containing element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The containing element, with the Custom Block form inserted.
   */
  public function processBlockForm(array $element, FormStateInterface $form_state) {
    $field_name = isset($element['#field_name']) ? $element['#field_name'] : NULL;
    $allowed_fields = isset($element['#allowed_fields']) ? $element['#allowed_fields'] : NULL;
    $delta = isset($element['#delta']) ? $element['#delta'] : NULL;
    if ($field_name && isset($element[$field_name])) {
      foreach (Element::children($element) as $key) {
        if (substr($key, 0, 10) === 'exo_field_' && !in_array($key, $allowed_fields)) {
          $element[$key]['#access'] = FALSE;
        }
        elseif (in_array($key, $allowed_fields)) {
          $definition = $this->exoComponentManager->getEntityBundleComponentDefinition($element['#block']->type->entity);
          $field = $definition->getFieldBySafeId($key);
          $component_field = $this->exoComponentManager->getExoComponentFieldManager()->loadInstance($field->getType());
          $component_field->componentFormAlter($element[$key], $form_state, $field);
          if ($field->supportsMultiple()) {
            // Do not treat as a multiple value form.
            unset($element[$key]['widget']['#theme']);
            unset($element[$key]['widget']['add_more']);
            foreach (Element::children($element[$key]['widget']) as $child_delta) {
              if ($delta != $child_delta) {
                $element[$key]['widget'][$child_delta]['#access'] = FALSE;
              }
              else {
                $element[$key]['widget'][$child_delta]['_weight']['#access'] = FALSE;
              }
            }
          }
        }
      }
    }
    else {
      foreach (Element::children($element) as $key) {
        if (substr($key, 0, 10) === 'exo_field_') {
          $definition = $this->exoComponentManager->getEntityBundleComponentDefinition($element['#block']->type->entity);
          $field = $definition->getFieldBySafeId($key);
          $component_field = $this->exoComponentManager->getExoComponentFieldManager()->loadInstance($field->getType());
          $component_field->componentFormAlter($element[$key], $form_state, $field);
        }
      }
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function processBlockFormElement(array $element, FormStateInterface $form_state) {
    if (isset($element['#field_name'])) {
      $field_name = $element['#field_name'];
      if (isset($element[$field_name])) {
        foreach (Element::children($element) as $key) {
          if (substr($key, 0, 10) === 'exo_field_' && $key !== $field_name) {
            $element[$key]['#access'] = FALSE;
          }
          elseif ($key === $field_name) {
            $definition = $this->exoComponentManager->getEntityBundleComponentDefinition($element['#block']->type->entity);
            $field = $definition->getFieldBySafeId($field_name);
            $component_field = $this->exoComponentManager->getExoComponentFieldManager()->loadInstance($field->getType());
            $component_field->componentFormAlter($element[$key], $form_state, $field);
          }
        }
      }
    }
    return $element;
  }

  /**
   * Submit form dialog #ajax callback.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response that display validation error messages or represents a
   *   successful submission.
   */
  public function ajaxAppearanceSubmit(array &$form, FormStateInterface $form_state) {
    if ($form_state->hasAnyErrors()) {
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -1000,
      ];
      $form['#sorted'] = FALSE;
      $response = new AjaxResponse();
      $response->addCommand(new ReplaceCommand('[data-drupal-selector="' . $form['#attributes']['data-drupal-selector'] . '"]', $form));
    }
    else {
      $response = $this->successfulAjaxAppearanceSubmit($form, $form_state);
    }
    return $response;
  }

  /**
   * Allows the form to respond to a successful AJAX submission.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response.
   */
  protected function successfulAjaxAppearanceSubmit(array $form, FormStateInterface $form_state) {
    $definition = $this->exoComponentManager->getEntityBundleComponentDefinition($this->inlineBlock->type->entity);
    $attributes = $this->exoComponentManager->getExoComponentPropertyManager()->getModifierAttributes($definition, $this->inlineBlock, TRUE);

    $response = new AjaxResponse();
    $response->addCommand(new ExoComponentModifierAttributesCommand($attributes));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  protected function submitLabel() {
    return $this->t('Update');
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $op = isset($trigger['#op']) ? $trigger['#op'] : 'submit';
    $definition = $this->exoComponentManager->getEntityBundleComponentDefinition($this->inlineBlock->type->entity);

    if ($modifier_values = $form_state->getValue('modifiers')) {
      if ($op == 'reset') {
        // Reset given modifier.
        $this->exoComponentManager->getExoComponentPropertyManager()->populateEntity($definition, $this->inlineBlock, $trigger['#field_reset']);
      }
      else {
        if (!$this->inlineBlock->get(ExoComponentPropertyManager::MODIFIERS_FIELD_NAME)->isEmpty()) {
          $original_values = $this->inlineBlock->get(ExoComponentPropertyManager::MODIFIERS_FIELD_NAME)->first()->value;
          $modifier_values = NestedArray::mergeDeep($original_values, $modifier_values);
        }
        $this->inlineBlock->get(ExoComponentPropertyManager::MODIFIERS_FIELD_NAME)->setValue(['value' => $modifier_values]);
      }
    }
    $definition = $this->exoComponentManager->getEntityBundleComponentDefinition($this->inlineBlock->type->entity);

    // Make sure target is set on parent.
    $this->setTargetEntity($this->inlineBlock, $form['settings']['block_form']['#block'], $this->parents);
    // Allow component to act on update.
    $this->exoComponentManager->onUpdateEntity($definition, $this->inlineBlock);
    // Temporarily store changes.
    $form_state->setTemporaryValue('inlineBlock', $this->inlineBlock);

    if (!empty($trigger['#do_submit'])) {
      $form['settings']['block_form']['#block'] = $this->inlineBlock;
      parent::submitForm($form, $form_state);
    }
    else {
      $form_state->setRebuild();
    }
  }

}
