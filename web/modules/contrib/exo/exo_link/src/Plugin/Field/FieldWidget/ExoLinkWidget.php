<?php

namespace Drupal\exo_link\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;
use Drupal\Component\Utility\Html;
use Drupal\exo_link\ExoLinkLinkitHelper;

/**
 * Plugin implementation of the 'link' widget.
 *
 * @FieldWidget(
 *   id = "exo_link",
 *   label = @Translation("eXo Link"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class ExoLinkWidget extends LinkWidget {

  /**
   * If linkit module exists.
   *
   * @var bool
   */
  protected $linkitExists;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'placeholder_url' => '',
      'placeholder_title' => '',
      'linkit' => FALSE,
      'linkit_profile' => 'default',
      'icon' => TRUE,
      'packages' => [],
      'target' => FALSE,
      'class' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    if ($this->linkitModuleExists()) {
      $element['linkit'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Use Linkit'),
        '#description' => $this->t('If selected, the linkit module will be used for URL autocomplete.'),
        '#default_value' => $this->getSetting('linkit'),
      ];

      $linkit_profiles = \Drupal::entityTypeManager()->getStorage('linkit_profile')->loadMultiple();
      $options = [];
      foreach ($linkit_profiles as $linkit_profile) {
        $options[$linkit_profile->id()] = $linkit_profile->label();
      }

      $element['linkit_profile'] = [
        '#type' => 'select',
        '#title' => $this->t('Linkit Profile'),
        '#default_value' => $this->getSetting('linkit_profile'),
        '#description' => $this->t('The Linkit profile that should be used when looking up content.'),
        '#options' => $options,
        '#states' => [
          'visible' => [
            ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][linkit]"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }

    $element['icon'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow icon selection'),
      '#description' => $this->t('If selected, icon selection will be enabled.'),
      '#default_value' => $this->getSetting('icon'),
    ];

    $element['packages'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Icon Packages'),
      '#default_value' => $this->getSetting('packages'),
      '#description' => $this->t('The icon packages that should be made available in this field. If no packages are selected, all will be made available.'),
      '#options' => $this->getPackageOptions(),
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][icon]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $element['target'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow target selection'),
      '#description' => $this->t('If selected, an "open in new window" checkbox will be made available.'),
      '#default_value' => $this->getSetting('target'),
    ];

    $element['class'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow adding custom CSS classes'),
      '#description' => $this->t('If selected, a textfield will be provided that will allow adding in custom CSS classes.'),
      '#default_value' => $this->getSetting('class'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['#element_validate'][] = [get_called_class(), 'validateElement'];
    $element['title']['#weight'] = -1;

    $item = $items[$delta];
    $options = $item->get('options')->getValue();
    $attributes = isset($options['attributes']) ? $options['attributes'] : [];

    if ($this->getSetting('linkit')) {
      $uri_as_displayable_string = static::getLinkitUriAsDisplayableString($item->uri);

      // The current field value could have been entered by a different user.
      // However, if it is inaccessible to the current user, do not display it
      // to them.
      $default_allowed = !$item->isEmpty() && (\Drupal::currentUser()->hasPermission('link to any page') || $item->getUrl()->access());

      $element['uri']['#type'] = 'linkit';
      $element['uri']['#after_build'][] = [get_class($this), 'linkitAfterBuild'];
      $element['uri']['#description'] = $this->t('Start typing to find content or paste a URL and click on the suggestion below.');
      $element['uri']['#autocomplete_route_name'] = 'linkit.autocomplete';
      $element['uri']['#autocomplete_route_parameters'] = [
        'linkit_profile_id' => $this->getSetting('linkit_profile'),
      ];
      $element['uri']['#error_no_message'] = TRUE;

      $element['attributes']['href'] = [
        '#type' => 'hidden',
        '#default_value' => $default_allowed ? $item->uri : '',
      ];

      if ($default_allowed && parse_url($item->uri, PHP_URL_SCHEME) == 'entity') {
        $entity = ExoLinkLinkitHelper::getEntityFromUri($item->uri);
      }

      $element['attributes']['data-entity-type'] = [
        '#type' => 'hidden',
        '#default_value' => $default_allowed && isset($entity) ? $entity->getEntityTypeId() : '',
      ];

      $element['attributes']['data-entity-uuid'] = [
        '#type' => 'hidden',
        '#default_value' => $default_allowed && isset($entity) ? $entity->uuid() : '',
      ];

      $element['attributes']['data-entity-substitution'] = [
        '#type' => 'hidden',
        '#default_value' => $default_allowed && isset($entity) ? $entity->getEntityTypeId() == 'file' ? 'file' : 'canonical' : '',
      ];
    }

    if ($this->getSetting('icon')) {
      $class_name = Html::getUniqueId('exo-link-widget-' . $this->fieldDefinition->getName() . '-' . $delta);
      $element['options']['attributes']['data-icon'] = [
        '#type' => 'exo_icon',
        '#title' => $this->t('Icon'),
        '#default_value' => isset($attributes['data-icon']) ? $attributes['data-icon'] : NULL,
        '#packages' => $this->getPackages(),
        '#attributes' => [
          'class' => [$class_name],
        ],
      ];

      $element['options']['attributes']['data-icon-position'] = [
        '#type' => 'select',
        '#title' => $this->t('Icon position'),
        '#options' => ['before' => $this->t('Before'), 'after' => $this->t('After')],
        '#default_value' => isset($attributes['data-icon-position']) ? $attributes['data-icon-position'] : 'before',
        '#required' => TRUE,
        '#states' => [
          'visible' => [
            '.' . $class_name => ['filled' => TRUE],
          ],
        ],
      ];
    }

    if ($this->getSetting('class')) {
      $element['options']['attributes']['class'] = [
        '#type' => 'textfield',
        '#title' => $this->t('CSS classes'),
        '#description' => $this->t('Enter space-separated CSS class names that will be added to the link.'),
        '#default_value' => !empty($attributes['class']) ? implode(' ', $attributes['class']) : NULL,
      ];
    }

    if ($this->getSetting('target')) {
      $element['options']['attributes']['target'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Open link in new window'),
        '#description' => $this->t('If selected, the menu link will open in a new window/tab when clicked.'),
        '#default_value' => !empty($attributes['target']),
        '#return_value' => '_blank',
      ];
    }

    if (!empty($element['options'])) {
      $element['options'] += [
        '#type' => 'fieldset',
        '#title' => $this->t('Options'),
        '#weight' => 100,
      ];
    }

    // If cardinality is 1, ensure a proper label is output for the field.
    if (!empty($element['options']) && $this->fieldDefinition->getFieldStorageDefinition()->getCardinality() == 1) {
      $element += [
        '#type' => 'fieldset',
      ];
      $element['uri']['#title'] = $this->t('URL');
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function validateUriElement($element, FormStateInterface $form_state, $form) {
    if (parse_url($element['#value'], PHP_URL_SCHEME) === 'internal' && !in_array($element['#value'][0], [
      '/',
      '?',
      '#',
    ], TRUE) && substr($element['#value'], 0, 7) !== '<front>') {
      $form_state->setError($element, t('Manually entered paths should start with /, ? or #.'));
      return;
    }
    // If value is not internet, make sure to unset attributes used by linkit.
    if (isset($element['#value'][0]) && parse_url($element['#value'], PHP_URL_SCHEME) !== 'internal' && (in_array($element['#value'][0], [
      '/',
      '?',
      '#',
    ], TRUE) || substr($element['#value'], 0, 7) === '<front>')) {
      $parents = array_slice($element['#parents'], 0, -1);
      $values = $form_state->getValue($parents);
      unset($values['attributes']['data-entity-type'], $values['attributes']['data-entity-uuid'], $values['attributes']['data-entity-substitution']);
      $form_state->setValue($parents, $values);
    }
  }

  /**
   * Swap out linkit library.
   *
   * This can be removed once linkit supports field widgets.
   */
  public static function linkitAfterBuild($element) {
    $element['#attributes']['class'][] = 'exo-link-linkit';
    $element['#attached']['library'][0] = 'exo_link/linkit';
    return $element;
  }

  /**
   * Get packages available to this field.
   */
  protected function getPackages() {
    return $this->getSetting('packages');
  }

  /**
   * Get packages as options.
   *
   * @return array
   *   An array of id => label options.
   */
  protected function getPackageOptions() {
    return \Drupal::service('exo_icon.repository')->getPackagesAsLabels();
  }

  /**
   * Recursively clean up options array if no data-icon is set.
   */
  public static function validateElement($element, FormStateInterface $form_state, $form) {
    $values = $form_state->getValue($element['#parents']);
    if (empty($values['options']['attributes']['data-icon'])) {
      $values['options']['attributes']['data-icon-position'] = '';
    }
    if (!empty($values)) {
      foreach ($values['options']['attributes'] as $attribute => $value) {
        if (!empty($value)) {
          if ($attribute == 'class') {
            $value = explode(' ', $value);
          }
          $values['options']['attributes'][$attribute] = $value;
          $values['attributes'][$attribute] = $value;
        }
        else {
          unset($values['options']['attributes'][$attribute]);
          unset($values['attributes'][$attribute]);
        }
      }
    }
    $form_state->setValueForElement($element, $values);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    if ($this->supportsInternalLinks() && $this->getSetting('linkit')) {
      $summary[] = $this->t('Use Linkit: %profile', ['%profile' => $this->getSetting('linkit_profile')]);
    }
    if ($this->getSetting('icon')) {
      $summary[] = $this->t('Allow icon selection');
      $enabled_packages = array_filter($this->getSetting('packages'));
      if ($enabled_packages) {
        $enabled_packages = array_intersect_key($this->getPackageOptions(), $enabled_packages);
        $summary[] = $this->t('With icon packages: %packages', ['%packages' => implode(', ', $enabled_packages)]);
      }
      else {
        $summary[] = $this->t('With icon packages: %packages', ['%packages' => 'All']);
      }
    }
    if ($this->getSetting('target')) {
      $summary[] = $this->t('Allow target selection');
    }
    if ($this->getSetting('class')) {
      $summary[] = $this->t('Allow custom CSS classes');
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // If linkit is not enabled, use default.
    if (!$this->getSetting('linkit')) {
      return parent::massageFormValues($values, $form, $form_state);
    }

    // The following code is ignored and is left only for future use.
    foreach ($values as &$value) {
      $value['uri'] = ExoLinkLinkitHelper::getUriFromSubmittedValue($value);
      $value += ['options' => []];
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  protected static function getLinkitUriAsDisplayableString($uri) {
    $scheme = parse_url($uri, PHP_URL_SCHEME);

    // By default, the displayable string is the URI.
    $displayable_string = $uri;

    // A different displayable string may be chosen in case of the 'internal:'
    // or 'entity:' built-in schemes.
    if ($scheme === 'internal') {
      $uri_reference = explode(':', $uri, 2)[1];

      // @todo '<front>' is valid input for BC reasons, may be removed by
      //   https://www.drupal.org/node/2421941
      $path = parse_url($uri, PHP_URL_PATH);
      if ($path === '/') {
        $uri_reference = '<front>' . substr($uri_reference, 1);
      }

      $displayable_string = $uri_reference;
    }
    elseif ($scheme === 'entity' && $entity = ExoLinkLinkitHelper::getEntityFromUri($uri)) {
      // If there is no fragment on the original URI, show the entity label.
      $fragment = parse_url($uri, PHP_URL_FRAGMENT);
      if (empty($fragment)) {
        $displayable_string = $entity->label();
      }
    }
    elseif ($scheme === 'mailto') {
      $email = explode(':', $uri)[1];
      $displayable_string = $email;
    }

    return $displayable_string;
  }

  /**
   * Check if linkit module exists.
   *
   * @return bool
   *   TRUE if linkit module exists.
   */
  protected function linkitModuleExists() {
    if (!isset($this->linkitExists)) {
      $this->linkitExists = \Drupal::service('module_handler')->moduleExists('linkit');
    }
    return $this->linkitExists;
  }

}
