<?php

namespace Drupal\exo_form\Plugin\WebformElement;

use Drupal\webform\Plugin\WebformElement\Number;

/**
 * Provides a 'exo_number' element.
 *
 * @WebformElement(
 *   id = "exo_number",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Number.php/class/Number",
 *   label = @Translation("eXo Number"),
 *   description = @Translation("Provides a form element for numeric input, with special numeric validation."),
 *   category = @Translation("Advanced elements"),
 * )
 */
class ExoNumber extends Number {

}
