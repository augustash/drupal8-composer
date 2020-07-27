<?php

namespace Drupal\exo_alchemist\Command;

use Drupal\Console\Command\Shared\ConfirmationTrait;
use Drupal\Console\Command\Shared\ThemeTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Console\Core\Generator\GeneratorInterface;
use Drupal\Console\Core\Utils\ChainQueue;
use Drupal\Console\Core\Utils\StringConverter;
use Drupal\Console\Extension\Manager;
use Drupal\Console\Utils\Validator;
use Symfony\Component\Console\Input\InputOption;
// @codingStandardsIgnoreStart
use Drupal\Console\Annotations\DrupalCommand;
// @codingStandardsIgnoreEND
use Drupal\Console\Command\Shared\ArrayInputTrait;
use Drupal\exo_alchemist\ExoComponentManager;

/**
 * Class ExoComponentCommand.
 *
 * @DrupalCommand (
 *   extension="exo_alchemist",
 *   extensionType="module"
 * )
 */
class ExoComponentCommand extends ContainerAwareCommand {
  use ThemeTrait;
  use ConfirmationTrait;
  use ArrayInputTrait;
  use ExoComponentFieldTrait;
  use ExoComponentModifierTrait;

  /**
   * The extention manager.
   *
   * @var \Drupal\Console\Extension\Manager
   */
  protected $extentionManager;

  /**
   * Drupal\Console\Core\Generator\GeneratorInterface definition.
   *
   * @var \Drupal\Console\Core\Generator\GeneratorInterface
   */
  protected $generator;

  /**
   * The eXo component manager.
   *
   * @var \Drupal\exo_alchemist\ExoComponentManager
   */
  protected $exoComponentManager;

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
   * Constructs a new ExoComponentCommand object.
   */
  public function __construct(Manager $extensionManager, GeneratorInterface $generator, ExoComponentManager $exo_component_manager, StringConverter $stringConverter, Validator $validator, ChainQueue $chainQueue) {
    $this->extensionManager = $extensionManager;
    $this->generator = $generator;
    $this->exoComponentManager = $exo_component_manager;
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
      ->setName('generate:exo:alchemist:component')
      ->setDescription('Drupal Console generated command.')
      ->setHelp($this->trans('commands.exo.alchemist.component.help'))
      ->addOption(
        'theme',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.common.options.theme')
      )
      ->addOption(
        'label',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.exo.alchemist.component.options.label')
      )
      ->addOption(
        'id',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.exo.alchemist.component.options.id')
      )
      ->addOption(
        'description',
        NULL,
        InputOption::VALUE_OPTIONAL,
        $this->trans('commands.exo.alchemist.component.options.description')
      )
      ->addOption(
        'category',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.exo.alchemist.component.options.category')
      )
      ->addOption(
        'restrict_access',
        NULL,
        InputOption::VALUE_OPTIONAL,
        $this->trans('commands.exo.alchemist.component.options.restrict_access')
      )
      ->addOption(
        'fields',
        null,
        InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
        $this->trans('commands.exo.alchemist.component.options.fields')
      )
      ->addOption(
        'modifiers',
        null,
        InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
        $this->trans('commands.exo.alchemist.component.options.modifiers')
      )
      ->setAliases(['eacg']);
  }

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $this->getThemeOption();

    $label = $input->getOption('label');
    if (!$label) {
      $label = $this->getIo()->ask(
        $this->trans('commands.exo.alchemist.component.questions.label')
      );
      $input->setOption('label', $label);
    }

    $id = $input->getOption('id');
    if (!$id) {
      $id = $this->getIo()->ask(
        $this->trans('commands.exo.alchemist.component.questions.id'),
        $this->stringConverter->createMachineName($label),
      );
      $input->setOption('id', $id);
    }

    $description = $input->getOption('description');
    if (!$description) {
      $description = $this->getIo()->askEmpty(
        $this->trans('commands.exo.alchemist.component.questions.description')
      );
      $input->setOption('description', $description);
    }

    $category = $input->getOption('category');
    if (!$category) {
      $category = $this->getIo()->ask(
        $this->trans('commands.exo.alchemist.component.questions.category'),
        'General'
      );
      $input->setOption('category', $category);
    }

    $restrict_access = $input->getOption('restrict_access');
    if (!$restrict_access) {
      $restrict_access = $this->getIo()->confirm(
        $this->trans(
          $this->trans('commands.exo.alchemist.component.questions.restrict_access')
        ),
        FALSE
      );
    }
    $input->setOption('restrict_access', $restrict_access);

    $fields = $input->getOption('fields');
    if (!$fields) {
      $fields = $this->fieldQuestion();
      $input->setOption('fields', $fields);
    } else {
      $fields= $this->explodeInlineArray($fields);
    }
    $input->setOption('fields', $fields);

    $modifiers = $input->getOption('modifiers');
    if (!$modifiers) {
      $modifiers = $this->modifierQuestion();
      $input->setOption('modifiers', $modifiers);
    } else {
      $modifiers= $this->explodeInlineArray($modifiers);
    }
    $input->setOption('modifiers', $modifiers);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    // @see use Drupal\Console\Command\Shared\ConfirmationTrait::confirmOperation
    if (!$this->confirmOperation()) {
      return 1;
    }
    // $this->getIo()->info('execute');
    // $this->getIo()->info($this->trans('commands.exo.alchemist.component.messages.success'));
    $this->generator->generate([
      'theme' => $input->getOption('theme'),
      'id' => $input->getOption('id'),
      'label' => $input->getOption('label'),
      'description' => $input->getOption('description'),
      'category' => $input->getOption('category'),
      'restrict_access' => $input->getOption('restrict_access'),
      'fields' => $input->getOption('fields'),
      'modifiers' => $input->getOption('modifiers'),
    ]);
  }

}
