<?php

namespace Drupal\exo_modal\Ajax;

use Drupal\Core\Ajax\CommandInterface;
use Drupal\Core\Ajax\CommandWithAttachedAssetsInterface;
use Drupal\Core\Ajax\CommandWithAttachedAssetsTrait;

/**
 * Defines an AJAX command to open certain content in a modal.
 *
 * @ingroup ajax
 */
class ExoModalOpenCommand implements CommandInterface, CommandWithAttachedAssetsInterface {

  use CommandWithAttachedAssetsTrait;

  /**
   * The unique id of this modal.
   *
   * @var string
   */
  protected $id = '';

  /**
   * The title of the modal.
   *
   * @var string
   */
  protected $title;

  /**
   * The content for the modal.
   *
   * Either a render array or an HTML string.
   *
   * @var string|array
   */
  protected $content;

  /**
   * Stores modal-specific options passed directly to modals.
   *
   * Any jQuery UI option can be used. See http://api.jqueryui.com/modal.
   *
   * @var array
   */
  protected $options;

  /**
   * Custom settings that will be passed to the Drupal behaviors.
   *
   * @var array
   */
  protected $settings;

  /**
   * The modal object.
   *
   * @var \Drupal\exo_modal\ExoModalInterface
   */
  protected $modal;

  /**
   * Constructs an OpenDialogCommand object.
   *
   * @param string $id
   *   The unique id of this modal.
   * @param string|array $content
   *   The content that will be placed in the modal, either a render array
   *   or an HTML string.
   * @param array $options
   *   (optional) Options to be passed to the modal implementation. Any
   *   jQuery UI option can be used. See http://api.jqueryui.com/modal.
   * @param array|null $settings
   *   (optional) Custom settings that will be passed to the Drupal behaviors
   *   on the content of the modal. If left empty, the settings will be
   *   populated automatically from the current request.
   */
  public function __construct($id, $content, array $options = [], $settings = NULL) {
    $this->id = $id;
    if (!is_array($content)) {
      $content = [
        'content' => ['#markup' => $content],
      ];
    }
    $content['messages'] = [
      '#type' => 'status_messages',
      '#weight' => -1000,
    ];
    $this->content = $content;
    $this->options = $options;
    $this->settings = $settings;
  }

  /**
   * Returns the modal options.
   *
   * @return array
   *   The options.
   */
  public function getOptions() {
    return $this->options + [
      'autoOpen' => TRUE,
      'destroyOnClose' => TRUE,
    ];
  }

  /**
   * Sets the modal options array.
   *
   * @param array $options
   *   Options to be passed to the modal implementation. Any jQuery UI option
   *   can be used. See http://api.jqueryui.com/modal.
   */
  public function setOptions(array $options) {
    $this->options = $options;
  }

  /**
   * Sets a single modal option value.
   *
   * @param string $key
   *   Key of the modal option. Any jQuery UI option can be used.
   *   See http://api.jqueryui.com/modal.
   * @param mixed $value
   *   Option to be passed to the modal implementation.
   */
  public function setOption($key, $value) {
    $this->options[$key] = $value;
  }

  /**
   * Sets the modal title (an alias of setOptions).
   *
   * @param string $title
   *   The new title of the modal.
   */
  public function setTitle($title) {
    $this->setOption('title', $title);
  }

  /**
   * Get the exo modal.
   */
  public function getModal() {
    if (!isset($this->modal)) {
      $this->modal = \Drupal::service('exo_modal.generator')->generate(
        $this->id,
        ['modal' => $this->getOptions()],
        $this->content
      );
    }
    return $this->modal;
  }

  /**
   * Implements \Drupal\Core\Ajax\CommandInterface:render().
   */
  public function render() {
    // Prepare modal.
    $modal = $this->getModal()->toRenderableModal();
    $modal['#attached']['library'][] = 'exo_modal/ajax';
    $this->content = $modal;

    return [
      'command' => 'exoModalInsert',
      'selector' => 'body',
      'data' => $this->getRenderedContent(),
      'settings' => $this->settings,
    ];
  }

}
