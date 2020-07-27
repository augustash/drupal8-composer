<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use CommerceGuys\Addressing\AddressFormat\FieldOverride;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFieldableBase;

/**
 * A 'text' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "address",
 *   label = @Translation("Text"),
 *   provider = "address"
 * )
 */
class Address extends ExoComponentFieldFieldableBase {

  /**
   * {@inheritdoc}
   */
  public function getStorageConfig() {
    $field = $this->getFieldDefinition();
    $available_countries = $field->getAdditionalValue('address_countries') ?: [
      'US' => 'US',
    ];
    $field_overrides = $field->getAdditionalValue('address_overrides') ?: [
      'givenName' => [
        'override' => FieldOverride::HIDDEN,
      ],
      'additionalName' => [
        'override' => FieldOverride::HIDDEN,
      ],
      'familyName' => [
        'override' => FieldOverride::HIDDEN,
      ],
      'organization' => [
        'override' => FieldOverride::HIDDEN,
      ],
    ];
    return [
      'type' => 'address',
      'settings' => [
        'available_countries' => $available_countries,
        'field_overrides' => $field_overrides,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetConfig() {
    return [
      'type' => 'address_default',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    return [
      'address1' => $this->t('Address 1'),
      'address2' => $this->t('Address 2'),
      'city' => $this->t('City'),
      'state' => $this->t('State'),
      'postal_code' => $this->t('Postal Code/Zip'),
      'country_code' => $this->t('Country Code'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue($delta = 0) {
    return [
      'address_line1' => '1600 Pennsylvania Ave NW',
      'address_line2' => '',
      'locality' => 'Washington',
      'administrative_area' => 'DC',
      'postal_code' => '20500',
      'country_code' => 'US',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    return [
      'address1' => $item->address_line1,
      'address2' => $item->address_line2,
      'city' => $item->locality,
      'state' => $item->administrative_area,
      'postal_code' => $item->postal_code,
      'country_code' => $item->country_code,
    ];
  }

}
