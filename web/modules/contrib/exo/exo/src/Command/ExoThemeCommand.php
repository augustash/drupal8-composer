<?php

namespace Drupal\exo\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Utils\Validator;
use Drupal\Console\Command\Shared\ModuleTrait;
use Drupal\Console\Extension\Manager;
use Drupal\Console\Core\Utils\StringConverter;
use Drupal\Console\Core\Utils\ChainQueue;
use Drupal\exo\Generator\ExoThemeGenerator;
// @codingStandardsIgnoreStart
use Drupal\Console\Annotations\DrupalCommand;
// @codingStandardsIgnoreEND

/**
 * Class ThemeCommand.
 *
 * @DrupalCommand (
 *   extension="exo",
 *   extensionType="module"
 * )
 */
class ExoThemeCommand extends ExoCommandBase {
  use ModuleTrait;

  /**
   * Drupal\Console\Extension\Manager definition.
   *
   * @var \Drupal\Console\Extension\Manager
   */
  protected $extensionManager;

  /**
   * Drupal\Console\Core\Generator\GeneratorInterface definition.
   *
   * @var \Drupal\Console\Core\Generator\GeneratorInterface
   */
  protected $generator;

  /**
   * Drupal\Console\Core\Utils\StringConverter definition.
   *
   * @var \Drupal\Console\Core\Utils\StringConverter
   */
  protected $stringConverter;

  /**
   * Drupal\Console\Utils\Validator definition.
   *
   * @var \Drupal\Console\Utils\Validator
   */
  protected $validator;

  /**
   * Drupal\Console\Core\Utils\ChainQueue definition.
   *
   * @var \Drupal\Console\Core\Utils\ChainQueue
   */
  protected $chainQueue;

  /**
   * Constructs a new ThemeCommand object.
   */
  public function __construct(
    Manager $extensionManager,
    ExoThemeGenerator $exo_theme_generator,
    StringConverter $stringConverter,
    Validator $validator,
    ChainQueue $chainQueue
  ) {
    $this->extensionManager = $extensionManager;
    $this->generator = $exo_theme_generator;
    $this->stringConverter = $stringConverter;
    $this->validator = $validator;
    $this->chainQueue = $chainQueue;
    parent::__construct();
  }

  /**
   * An array of color options.
   */
  protected function getColorOptions() {
    return [
      'base' => '#373a3c',
      'offset' => '#f1f1f1',
      'primary' => '#2780e3',
      'secondary' => '#373a3c',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('generate:exo:theme')
      ->setDescription($this->trans('Generate an eXo theme.'))
      ->setHelp($this->trans('commands.exo.theme.help'))
      ->addOption(
        'module',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.common.options.module')
      )
      ->addOption(
        'class',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.exo.theme.options.class')
      )
      ->addOption(
        'label',
        NULL,
        InputOption::VALUE_OPTIONAL,
        $this->trans('commands.exo.theme.options.label')
      )
      ->addOption(
        'plugin-id',
        NULL,
        InputOption::VALUE_OPTIONAL,
        $this->trans('commands.exo.theme.options.plugin-id')
      )
      ->addOption(
        'gulp',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.exo.common.options.gulp')
      )
      ->setAliases(['exot']);

    foreach ($this->getColorOptions() as $color => $hex) {
      $this->addOption(
        $color . '-color',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.exo.theme.options.' . $color . '-color')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $this->getModuleOption();

    // --class option.
    $class_name = $input->getOption('class');
    if (!$class_name) {
      $class_name = $this->getIo()->ask(
        $this->trans('commands.exo.theme.options.class'),
        'ThemeName',
        function ($class_name) {
            return $this->validator->validateClassName($class_name);
        }
      );
      $input->setOption('class', $class_name);
    }

    // --plugin label option.
    $label = $input->getOption('label');
    if (!$label) {
      $label = $this->getIo()->ask(
        $this->trans('commands.exo.theme.questions.label'),
        ucwords($this->stringConverter->camelCaseToHuman($class_name))
      );
      $input->setOption('label', $label);
    }

    // --plugin-id option.
    $plugin_id = $input->getOption('plugin-id');
    if (!$plugin_id) {
      $plugin_id = $this->getIo()->ask(
        $this->trans('commands.exo.theme.questions.plugin-id'),
          $this->stringConverter->camelCaseToUnderscore($class_name)
      );
      $input->setOption('plugin-id', $plugin_id);
    }

    // --[type]-color options.
    foreach ($this->getColorOptions() as $color => $hex) {
      $value = $input->getOption($color . '-color');
      if (!$value) {
        $value = $this->getIo()->ask(
          $this->trans('commands.exo.theme.questions.' . $color . '-color'),
          $hex,
          function ($value) {
              return $this->validateHex($value);
          }
        );
        $input->setOption($color . '-color', $value);
      }
    }

    // --gulp option.
    $gulp = $input->getOption('gulp');
    if (!$gulp) {
      $gulp = $this->getIo()->confirm(
        $this->trans('commands.exo.common.questions.gulp'),
        'true'
      );
      $input->setOption('gulp', $gulp);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    // @see use Drupal\Console\Command\Shared\ConfirmationTrait::confirmOperation
    if (!$this->confirmOperation()) {
      return 1;
    }

    $parameters = [
      'module' => $input->getOption('module'),
      'class_name' => $this->validator->validateClassName($input->getOption('class')),
      'label' => ucwords($input->getOption('label')),
      'plugin_id' => $input->getOption('plugin-id'),
      'gulp' => $input->getOption('gulp'),
    ];

    foreach ($this->getColorOptions() as $color => $hex) {
      $parameters[$color . '_color'] = $input->getOption($color . '-color');
    }

    $this->generator->generate($parameters);

    $this->chainQueue->addCommand('cache:rebuild', ['cache' => 'discovery']);

    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function validateHex($hex) {
    if (preg_match('/^#([a-f0-9]{3}){1,2}\b/i', $hex)) {
      return $hex;
    }
    elseif (preg_match('/^([a-f0-9]{3}){1,2}\b/i', $hex)) {
      return '#' . $hex;
    }
    else {
      throw new \InvalidArgumentException(
        sprintf(
          'Value "%s" is invalid. It must be a valid CSS HEX color.',
          $hex
        )
      );
    }
  }

}
