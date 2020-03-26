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
use Drupal\exo\Generator\ExoThemeProviderGenerator;
// @codingStandardsIgnoreStart
use Drupal\Console\Annotations\DrupalCommand;
// @codingStandardsIgnoreEND

/**
 * Class ThemeProviderCommand.
 *
 * @DrupalCommand (
 *   extension="exo",
 *   extensionType="module"
 * )
 */
class ExoThemeProviderCommand extends ExoCommandBase {
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
    ExoThemeProviderGenerator $exo_theme_provider_generator,
    StringConverter $stringConverter,
    Validator $validator,
    ChainQueue $chainQueue
  ) {
    $this->extensionManager = $extensionManager;
    $this->generator = $exo_theme_provider_generator;
    $this->stringConverter = $stringConverter;
    $this->validator = $validator;
    $this->chainQueue = $chainQueue;
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('generate:exo:theme:provider')
      ->setDescription($this->trans('Generate an eXo theme provider.'))
      ->setHelp($this->trans('commands.exo.theme.provider.help'))
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
        $this->trans('commands.exo.theme.provider.options.class')
      )
      ->addOption(
        'plugin-id',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.exo.theme.provider.options.plugin-id')
      )
      ->addOption(
        'gulp',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.exo.common.options.gulp')
      )
      ->setAliases(['exotp']);
  }

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $module_name = $this->getModuleOption();

    // --class option.
    $class_name = $input->getOption('class');
    if (!$class_name) {
      $class_name = $this->getIo()->ask(
        $this->trans('commands.exo.theme.provider.options.class'),
        ucfirst($this->stringConverter->underscoreToCamelCase($module_name)) . 'ThemeProvider',
        function ($class_name) {
            return $this->validator->validateClassName($class_name);
        }
      );
      $input->setOption('class', $class_name);
    }

    // --plugin-id option.
    $plugin_id = $input->getOption('plugin-id');
    if (!$plugin_id) {
      $plugin_id = $this->getIo()->ask(
        $this->trans('commands.exo.theme.provider.questions.plugin-id'),
        $module_name
      );
      $input->setOption('plugin-id', $plugin_id);
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
      'plugin_id' => $input->getOption('plugin-id'),
      'gulp' => $input->getOption('gulp'),
    ];

    $this->generator->generate($parameters);

    $this->chainQueue->addCommand('cache:rebuild', ['cache' => 'discovery']);

    return 0;
  }

}
