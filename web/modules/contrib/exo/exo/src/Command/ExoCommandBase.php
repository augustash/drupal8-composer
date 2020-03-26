<?php

namespace Drupal\exo\Command;

use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Console\Command\Shared\ConfirmationTrait;

/**
 * Class ExoCommandBase.
 *
 * @package Drupal\Console\Generator
 */
abstract class ExoCommandBase extends ContainerAwareCommand {
  use ConfirmationTrait;

}
