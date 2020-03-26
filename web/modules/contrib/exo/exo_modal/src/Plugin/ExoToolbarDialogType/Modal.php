<?php

namespace Drupal\exo_modal\Plugin\ExoToolbarDialogType;

use Drupal\exo_toolbar\Plugin\ExoToolbarDialogTypeBase;
use Drupal\exo_toolbar\Entity\ExoToolbarItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\exo\ExoSettingsInterface;
use Drupal\exo_toolbar\ExoToolbarElementInterface;
use Drupal\exo_modal\ExoModalGeneratorInterface;

/**
 * Plugin implementation of the 'tip' eXo toolbar dialog type.
 *
 * @ExoToolbarDialogType(
 *   id = "modal",
 *   label = @Translation("Modal"),
 * )
 */
class Modal extends ExoToolbarDialogTypeBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\exo\ExoSettingsInterface definition.
   *
   * @var \Drupal\exo\ExoSettingsInterface
   */
  protected $exoSettings;

  /**
   * The eXo modal generator.
   *
   * @var \Drupal\exo_modal\ExoModalGeneratorInterface
   */
  protected $exoModalGenerator;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ExoSettingsInterface $exo_settings, ExoModalGeneratorInterface $exo_modal_generator) {
    $this->configuration = $configuration;
    $this->pluginId = $plugin_id;
    $this->pluginDefinition = $plugin_definition;
    $this->exoSettings = $exo_settings->createInstance($configuration);
    $this->exoModalGenerator = $exo_modal_generator;
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('exo_modal.settings'),
      $container->get('exo_modal.generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'exo_default' => TRUE,
      'exo_preset' => NULL,
      'settings' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function dialogTypeForm(array $form, FormStateInterface $form_state) {
    $form = $this->exoSettings->buildForm($form, $form_state);
    $form['settings']['#type'] = 'container';
    unset($form['settings']['trigger']);
    $form['settings']['modal']['timeout']['autoOpen']['#access'] = FALSE;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function dialogTypeValidate(array $form, FormStateInterface $form_state) {
    $this->exoSettings->validateForm($form, $form_state);
    parent::dialogTypeValidate($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function dialogTypeSubmit(array $form, FormStateInterface $form_state) {
    $this->exoSettings->submitForm($form, $form_state);
    parent::dialogTypeSubmit($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function elementPrepare(ExoToolbarElementInterface $element) {
    parent::elementPrepare($element);
    $element->addLibrary('exo_modal/toolbar.dialog');
  }

  /**
   * {@inheritdoc}
   */
  public function dialogBuild(ExoToolbarItemInterface $exo_toolbar_item) {
    $build = parent::dialogBuild($exo_toolbar_item);
    $modal = $this->exoModalGenerator->generate(
      'exo_toolbar_dialog_' . $exo_toolbar_item->id(),
      $this->exoSettings->getLocalSettings(),
      $build
    );
    $this->configuration['exo_modal_id'] = $modal->getId();
    return $modal->toRenderableModal();
  }

}
