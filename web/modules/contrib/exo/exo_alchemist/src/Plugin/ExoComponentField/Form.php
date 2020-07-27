<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldComputedBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Template\Attribute;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldDisplayFormTrait;

/**
 * A 'form' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "form",
 *   label = @Translation("Form"),
 * )
 */
class Form extends ExoComponentFieldComputedBase implements ContainerFactoryPluginInterface {

  use ExoComponentFieldDisplayFormTrait;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The form.
   *
   * @var [type]
   */
  protected $form;

  /**
   * Creates a PageTitle instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition() {
    parent::processDefinition();
    $field = $this->getFieldDefinition();
    if (!$field->hasAdditionalValue('form_class')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [form_class] be set.', $field->getType()));
    }
    if (!$field->hasAdditionalValue('form_args')) {
      $field->setAdditionalValue('form_args', []);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = [
      'render' => $this->t('The form renderable.'),
      'form.tag' => $this->t('Will be "form" when in full view and "div" in preview.'),
      'form.builder' => $this->t('Contains the elements required for proper form submission.'),
      'form.action' => $this->t('The form action url.'),
    ];
    $form = $this->getForm();
    foreach ($this->formProperties() as $key => $label) {
      $properties['form.' . $key] = $label;
    }
    $builder_children = $this->formBuilderChildren();
    foreach (Element::children($form) as $key) {
      if (!isset($builder_children[$key])) {
        $properties['form.field.' . $key] = $this->t('Form field with key of %key', [
          '%key' => $key,
        ]);
      }
    }
    return $properties;
  }

  /**
   * The form properties to extract.
   */
  protected function formProperties() {
    return [
      'method' => $this->t('The form method.'),
      'attributes' => $this->t('The form attributes.'),
    ];
  }

  /**
   * The form children that will make of the builder.
   */
  protected function formBuilderChildren() {
    return [
      'form_build_id' => $this->t('The form build id.'),
      'form_token' => $this->t('The form token.'),
      'form_id' => $this->t('The form id.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(ContentEntityInterface $entity, array $contexts) {
    $value = [];
    $form = $this->getForm();
    $is_layout_builder = $this->isLayoutBuilder($contexts);
    if ($is_layout_builder) {
      $render = $this->getFormAsPlaceholder((array) $form);
    }
    else {
      $render = $form;
    }
    // Rendered form.
    $value['render'] = $render;

    // Individual form options.
    $builder_children = $this->formBuilderChildren();
    $value['form'] = [];
    $value['form']['tag'] = $is_layout_builder ? 'div' : 'form';
    foreach ($this->formProperties() as $key => $label) {
      if (!isset($builder_children[$key])) {
        $value['form'][$key] = $form['#' . $key];
      }
    }
    $value['form']['field'] = [];
    foreach (Element::children($form) as $key) {
      $value['form']['field'][$key] = $form[$key];
    }

    $value['form']['builder'] = [
      '#attached' => $form['#attached'],
    ];
    foreach ($builder_children as $key => $label) {
      if (isset($form[$key])) {
        $value['form']['builder'][$key] = $form[$key];
      }
    }

    if (isset($form['#action'])) {
      $value['form']['attributes']['action'] = UrlHelper::stripDangerousProtocols($form['#action']);
    }
    $value['form']['attributes']['method'] = $form['#method'];
    $value['form']['attributes']['accept-charset'] = 'UTF-8';
    $value['form']['attributes'] = new Attribute($value['form']['attributes']);
    return $value;
  }

  /**
   * Get a form class.
   */
  protected function getForm() {
    if (!isset($this->form)) {
      $field = $this->getFieldDefinition();
      $args = array_merge([
        $field->getAdditionalValue('form_class'),
      ], $field->getAdditionalValue('form_args'));
      $this->form = call_user_func_array([$this->formBuilder, 'getForm'], $args);
      foreach (Element::children($this->form) as $key) {
        unset($this->form[$key]['#attributes']['autofocus']);
      }
    }
    return $this->form;
  }

}
