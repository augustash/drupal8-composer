<?php

namespace Drupal\exo_alchemist\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the UniqueInteger constraint.
 */
class ExoComponentTagValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    $valid = FALSE;
    foreach ($items as $tag) {
      foreach ($constraint->getTags() as $definition_tag) {
        if ($tag === $definition_tag) {
          $valid = TRUE;
        }
      }
    }
    if (!$valid) {
      $this->context->addViolation($constraint->notFound, ['%value' => $tag]);
    }
  }

}
