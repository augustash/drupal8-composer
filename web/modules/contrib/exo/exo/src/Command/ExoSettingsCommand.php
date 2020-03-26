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
use Drupal\exo\Generator\ExoSettingsGenerator;
use Drupal\Core\Routing\RouteProviderInterface;
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
class ExoSettingsCommand extends ExoCommandBase {
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
    ExoSettingsGenerator $exo_settings_generator,
    StringConverter $stringConverter,
    Validator $validator,
    ChainQueue $chainQueue,
    RouteProviderInterface $routeProvider
  ) {
    $this->extensionManager = $extensionManager;
    $this->generator = $exo_settings_generator;
    $this->stringConverter = $stringConverter;
    $this->validator = $validator;
    $this->chainQueue = $chainQueue;
    $this->routeProvider = $routeProvider;
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('generate:exo:settings')
      ->setDescription($this->trans('Generate an eXo settings service.'))
      ->setHelp($this->trans('commands.exo.settings.help'))
      ->addOption(
        'module',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.common.options.module')
      )
      ->addOption(
        'label',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.exo.settings.options.label')
      )
      ->addOption(
        'id',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.exo.settings.options.id')
      )
      ->addOption(
        'class',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.exo.settings.options.class')
      )
      ->addOption(
        'path',
        NULL,
        InputOption::VALUE_OPTIONAL,
        $this->trans('commands.exo.settings.options.path')
      )
      ->setAliases(['exos']);
  }

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $module = $this->getModuleOption();

    // --label option.
    $label = $input->getOption('label');
    if (!$label) {
      $label = $this->getIo()->ask(
        $this->trans('commands.exo.settings.questions.label'),
        ucwords($this->stringConverter->camelCaseToHuman($this->stringConverter->underscoreToCamelCase($module)))
      );
      $input->setOption('label', $label);
    }

    // --id option.
    $id = $input->getOption('id');
    if (!$id) {
      $id = $this->getIo()->ask(
        $this->trans('commands.exo.settings.questions.id'),
          $this->stringConverter->camelCaseToUnderscore($module) . '.settings'
      );
      $input->setOption('id', $id);
    }

    // --class option.
    $class = $input->getOption('class');
    if (!$class) {
      $class = $this->getIo()->ask(
        $this->trans('commands.exo.settings.questions.class'),
        ucfirst($this->stringConverter->underscoreToCamelCase(str_replace('.', '_', $id))),
        function ($class) {
            return $this->validator->validateClassName($class);
        }
      );
      $input->setOption('class', $class);
    }

    $path = $input->getOption('path');
    if (!$path) {
      $form_path = sprintf(
        '/admin/config/exo/%s',
        str_replace('_', '-', $this->stringConverter->camelCaseToUnderscore($this->stringConverter->humanToCamelCase($label)))
      );
      $path = $this->getIo()->ask(
        $this->trans('commands.exo.settings.questions.path'),
        $form_path,
        function ($path) {
          if (count($this->routeProvider->getRoutesByPattern($path)) > 0) {
            throw new \InvalidArgumentException(
              sprintf(
                $this->trans(
                  'commands.generate.form.messages.path-already-added'
                ),
                $path
              )
            );
          }

          return $path;
        }
      );
      $input->setOption('path', $path);
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
      'label' => $input->getOption('label'),
      'path' => $input->getOption('path'),
      'id' => $input->getOption('id'),
      'class' => $this->validator->validateClassName($input->getOption('class')),
    ];

    $this->generator->generate($parameters);
    $this->chainQueue->addCommand('cache:rebuild', ['cache' => 'discovery']);

    return 0;
  }

}
