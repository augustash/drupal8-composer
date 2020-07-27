<?php

namespace Drupal\exo\Plugin;

use Drupal\Core\Plugin\PluginBase;

/**
 * Class ThrobberBase.
 */
abstract class ExoThrobberPluginBase extends PluginBase implements ExoThrobberPluginInterface {

  /**
   * The path to exo.
   *
   * @var string
   */
  protected $path;

  /**
   * The markup for the throbber.
   *
   * @var mixed
   */
  protected $markup;

  /**
   * The CSS file path.
   *
   * @var string
   */
  protected $cssFile;

  /**
   * The plugin label.
   *
   * @var string
   */
  protected $label;

  /**
   * ThrobberPluginBase constructor.
   *
   * @param array $configuration
   *   Array with configuration.
   * @param string $plugin_id
   *   String with plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition value.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->path = '/' . drupal_get_path('module', 'exo');
    $this->markup = $this->setMarkup();
    $this->cssFile = $this->setCssFile();
  }

  /**
   * Function to get markup.
   *
   * @return mixed
   *   Return markup.
   */
  public function getMarkup() {
    return $this->markup;
  }

  /**
   * Function to get css file.
   *
   * @return mixed
   *   Return the css file.
   */
  public function getCssFile() {
    return $this->cssFile;
  }

  /**
   * Function to get label.
   *
   * @return mixed
   *   Return the label.
   */
  public function getLabel() {
    return $this->configuration['label'];
  }

  /**
   * Sets markup for throbber.
   */
  protected abstract function setMarkup();

  /**
   * Sets css file for throbber.
   */
  protected abstract function setCssFile();

}
