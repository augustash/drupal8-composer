<?php

namespace Drupal\exo_icon;

use Drupal\Core\Render\RenderableInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Render\Markup;

/**
 * Defines an eXo icon.
 */
class ExoIcon implements ExoIconInterface, RenderableInterface {

  /**
   * The icon definition.
   *
   * @var string
   */
  protected $definition;

  /**
   * The Attribute object.
   *
   * @var \Drupal\Core\Template\Attribute
   */
  protected $attributes;

  /**
   * Constructs a new ExoIcon.
   */
  public function __construct(array $definition) {
    $this->attributes = new Attribute();
    $this->definition = $definition;
  }

  /**
   * Creates new ExoIcon instance.
   */
  public static function create(array $definition) {
    return new static($definition);
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->definition['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->definition['type'];
  }

  /**
   * {@inheritdoc}
   */
  public function isSvg() {
    return $this->getType() == 'image';
  }

  /**
   * {@inheritdoc}
   */
  public function isFont() {
    return $this->getType() == 'font';
  }

  /**
   * {@inheritdoc}
   */
  public function getPackageId() {
    return $this->definition['package_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPrefix() {
    return $this->definition['prefix'];
  }

  /**
   * {@inheritdoc}
   */
  public function getTag() {
    return $this->definition['tag'];
  }

  /**
   * {@inheritdoc}
   */
  public function getSelector() {
    return $this->getPrefix() . $this->getTag();
  }

  /**
   * {@inheritdoc}
   */
  public function getHex() {
    return $this->isFont() ? '\\' . dechex($this->definition['properties']['code']) : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getWrappingElement() {
    return $this->isSvg() ? 'svg' : 'i';
  }

  /**
   * {@inheritdoc}
   */
  public function getChildren() {
    $build = [];
    if ($this->isFont()) {
      // Font glyphs cannot have more than one color by default. Using CSS,
      // IcoMoon layers multiple glyphs on top of each other to implement
      // multicolor glyphs. As a result, these glyphs take more than one
      // character code and cannot have ligatures. To avoid multicolor glyphs,
      // reimport your SVG after changing all its colors to the same color.
      if (!empty($this->definition['properties']['codes']) && count($this->definition['properties']['codes'])) {
        for ($i = 1; $i <= count($this->definition['properties']['codes']); $i++) {
          $build[]['#markup'] = '<span class="path' . $i . '"></span>';
        }
      }
    }
    else {
      $build['#markup'] = Markup::create('<use xlink:href="' . $this->definition['path'] . '/symbol-defs.svg#' . $this->getSelector() . '"></use>');
      $build['#allowed_tags'] = ['use', 'xlink'];
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function addClass($classes) {
    $this->attributes->addClass($classes);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeClass() {
    $this->attributes->removeClass($classes);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setAttributes(array $attributes) {
    $this->attributes = new Attribute($attributes);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setAttribute($attribute, $value) {
    $this->attributes->setAttribute($attribute, $value);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function toRenderable() {
    return [
      '#theme' => 'exo_icon__' . $this->getType(),
      '#icon' => $this,
      '#attributes' => $this->attributes->toArray(),
    ];
  }

}
