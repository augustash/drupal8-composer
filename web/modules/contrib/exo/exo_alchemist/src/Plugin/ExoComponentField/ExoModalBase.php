<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFieldableBase;
use Drupal\exo_modal\ExoModalGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A base class that modal links can extend.
 */
abstract class ExoModalBase extends ExoComponentFieldFieldableBase implements ContainerFactoryPluginInterface {

  /**
   * The eXo modal generator.
   *
   * @var \Drupal\exo_modal\ExoModalGeneratorInterface
   */
  protected $exoModalGenerator;

  /**
   * Creates a ExoModalBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\exo_modal\ExoModalGeneratorInterface $exo_modal_generator
   *   The eXo modal generator.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ExoModalGeneratorInterface $exo_modal_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->exoModalGenerator = $exo_modal_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('exo_modal.generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getStorageConfig() {
    return [
      'type' => 'exo_modal_meta',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetConfig() {
    return [
      'type' => 'exo_modal_meta',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = [
      'render' => $this->t('The modal link renderable.'),
    ];
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue($delta = 0) {
    $field = $this->getFieldDefinition();
    return [
      'trigger_text' => $this->t('Trigger text for @label', [
        '@label' => strtolower($field->getLabel()),
      ]),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    $field = $this->getFieldDefinition();
    $id = str_replace(['_', ' '], '-', implode('-', [
      $field->id(),
      $item->getEntity()->id(),
      $delta,
    ]));
    $url = $this->getModalUrl($item, $delta, $contexts);
    $settings = $item->settings ? $item->settings : [];
    $modal = $this->exoModalGenerator->generate($id, $settings);
    $modal->setTrigger($item->trigger_text, $item->trigger_icon);
    $modal->setSetting(['modal', 'title'], $item->modal_title);
    $modal->setSetting(['modal', 'subtitle'], $item->modal_subtitle);
    $modal->setSetting(['modal', 'icon'], $item->modal_icon);
    $modal->setSetting(['modal', 'contentAjax'], $url->getInternalPath());
    $modal->addModalClass(Html::getClass('exo-component-' . $field->getComponent()->getName() . '-modal'));
    $modal->addModalClass(Html::getClass('modal--' . $field->getName()));
    return [
      'render' => $modal->toRenderable(),
    ];
  }

  /**
   * Get the modal url.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item.
   * @param string $delta
   *   The field item delta.
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   *
   * @return \Drupal\Core\Url
   *   A url that will be set is the modal ajax url.
   */
  abstract protected function getModalUrl(FieldItemInterface $item, $delta, array $contexts);

}
