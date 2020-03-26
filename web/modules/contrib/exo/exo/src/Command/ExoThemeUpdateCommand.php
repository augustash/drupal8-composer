<?php

namespace Drupal\exo\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Utils\ChainQueue;
use Drupal\exo\Generator\ExoThemeUpdateGenerator;
use Drupal\exo\ExoThemePluginManagerInterface;
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
class ExoThemeUpdateCommand extends ExoCommandBase {

  /**
   * Drupal\Console\Core\Generator\GeneratorInterface definition.
   *
   * @var \Drupal\Console\Core\Generator\GeneratorInterface
   */
  protected $generator;

  /**
   * Drupal\exo\ExoThemePluginManagerInterface definition.
   *
   * @var \Drupal\exo\ExoThemePluginManagerInterface
   */
  protected $exoThemeManager;

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
    ExoThemeUpdateGenerator $exo_theme_update_generator,
    ExoThemePluginManagerInterface $exoThemeManager,
    ChainQueue $chainQueue
  ) {
    $this->generator = $exo_theme_update_generator;
    $this->exoThemeManager = $exoThemeManager;
    $this->chainQueue = $chainQueue;
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('exo:theme:update')
      ->setDescription($this->trans('Update an eXo theme.'))
      ->setHelp($this->trans('commands.exo.theme.update.help'))
      ->addOption(
        'theme',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.common.options.theme')
      )
      ->setAliases(['exotu']);
  }

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $theme_list = $this->getExoThemeList();
    if (empty($theme_list)) {
      throw new \Exception(
        sprintf(
          $this->trans(
            'commands.exo.common.messages.no-themes'
          ),
          $module
        )
      );
    }

    // --theme option.
    $theme = $input->getOption('theme');
    if (!$theme) {
      $theme = $this->getIo()->choiceNoList(
        $this->trans('commands.exo.common.options.theme'),
        $theme_list
      );
      $input->setOption('theme', $theme);
    }
    else {
      if (!isset($theme_list[$theme])) {
        throw new \Exception(
          sprintf(
            $this->trans(
              'commands.exo.common.messages.no-theme'
            ),
            $theme
          )
        );
      }
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
      'theme' => $input->getOption('theme'),
    ];

    $updated_count = $this->generator->generate($parameters);
    if ($updated_count === 0) {
      $this->getIo()->info(
        sprintf(
          $this->trans(
            'commands.exo.common.messages.no-theme-updates'
          ),
          $parameters['theme']
        )
      );
    }
    else {
      $theme_definition = $this->getExoTheme($parameters['theme']);
      $this->getIo()->newLine();
      $this->getIo()->info(
        sprintf(
          $this->trans(
            'commands.exo.common.messages.run-gulp'
          ),
          $theme_definition['providerPath']
        )
      );
      $this->getIo()->comment('gulp build');
      $this->getIo()->newLine();
    }

    return 0;
  }

  /**
   * Get list of available themes.
   *
   * @return array
   *   An array of theme ids.
   */
  protected function getExoThemeList() {
    return array_map(function ($plugin) {
      return $plugin['id'];
    }, $this->exoThemeManager->getDefinitions());
  }

  /**
   * Get a theme definition.
   *
   * @return array
   *   An array of theme ids.
   */
  protected function getExoTheme($theme_id) {
    $definitions = $this->exoThemeManager->getDefinitions();
    return isset($definitions[$theme_id]) ? $definitions[$theme_id] : NULL;
  }

}
