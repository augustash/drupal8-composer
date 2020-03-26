<?php

namespace Drupal\exo_modal\Plugin\WebformElement;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\ElementInfoManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\exo\ExoSettingsInterface;
use Drupal\exo_modal\ExoModalGeneratorInterface;
use Drupal\webform\Plugin\WebformElement\ContainerBase;
use Drupal\webform\Plugin\WebformElementManagerInterface;
use Drupal\webform\WebformLibrariesManagerInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\WebformTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'flexbox' element.
 *
 * @WebformElement(
 *   id = "exo_modal",
 *   default_key = "modal",
 *   label = @Translation("eXo Modal"),
 *   description = @Translation("Provides a eXo modal container."),
 *   category = @Translation("Containers"),
 * )
 */
class ExoModal extends ContainerBase {

  /**
   * The eXo Modal options service.
   *
   * @var \Drupal\exo\ExoSettingsPluginInstanceInterface
   */
  protected $exoModalSettings;

  /**
   * The eXo modal generator.
   *
   * @var \Drupal\exo_modal\ExoModalGeneratorInterface
   */
  protected $exoModalGenerator;

  /**
   * WebformManagedFileBase constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Render\ElementInfoManagerInterface $element_info
   *   The element info manager.
   * @param \Drupal\webform\Plugin\WebformElementManagerInterface $element_manager
   *   The webform element manager.
   * @param \Drupal\webform\WebformTokenManagerInterface $token_manager
   *   The webform token manager.
   * @param \Drupal\webform\WebformLibrariesManagerInterface $libraries_manager
   *   The webform libraries manager.
   * @param \Drupal\exo\ExoSettingsInterface $exo_modal_settings
   *   The eXo options service.
   * @param \Drupal\exo_modal\ExoModalGeneratorInterface $exo_modal_generator
   *   The eXo modal generator.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerInterface $logger, ConfigFactoryInterface $config_factory, AccountInterface $current_user, EntityTypeManagerInterface $entity_type_manager, ElementInfoManagerInterface $element_info, WebformElementManagerInterface $element_manager, WebformTokenManagerInterface $token_manager, WebformLibrariesManagerInterface $libraries_manager, ExoSettingsInterface $exo_modal_settings, ExoModalGeneratorInterface $exo_modal_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger, $config_factory, $current_user, $entity_type_manager, $element_info, $element_manager, $token_manager, $libraries_manager);
    $this->exoModalSettings = $exo_modal_settings->createInstance([]);
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
      $container->get('logger.factory')->get('webform'),
      $container->get('config.factory'),
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.element_info'),
      $container->get('plugin.manager.webform.element'),
      $container->get('webform.token_manager'),
      $container->get('webform.libraries_manager'),
      $container->get('exo_modal.settings'),
      $container->get('exo_modal.generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
      // Title.
      'title_display' => '',
      'help_display' => '',
      // Modal.
      'exo_default' => FALSE,
      'exo_preset' => '',
    ] + $this->modalSettingPropertiesToWebformProperties($this->exoModalSettings->getDefaultSettings()) + parent::getDefaultProperties();
  }

  /**
   * {@inheritdoc}
   */
  protected function modalSettingPropertiesToWebformProperties($properties, $prefix = '') {
    $return = [];
    foreach ($properties as $key => $property) {
      if ($prefix) {
        $key = $prefix . '__' . $key;
      }
      if (is_array($property)) {
        $return += $this->modalSettingPropertiesToWebformProperties($property, $key);
      }
      else {
        $return[$key] = $property;
      }
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  protected function build($format, array &$element, WebformSubmissionInterface $webform_submission, array $options = []) {
    /** @var \Drupal\webform\WebformSubmissionViewBuilderInterface $view_builder */
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('webform_submission');
    return $view_builder->buildElements($element, $webform_submission, $options, $format);
  }

  /**
   * {@inheritdoc}
   */
  public function preview() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['modal'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Modal'),
    ];
    // This is not working yet.
    $form['modal'] = $this->exoModalSettings->buildForm($form['modal'], $form_state);
    return $form;
  }

}
