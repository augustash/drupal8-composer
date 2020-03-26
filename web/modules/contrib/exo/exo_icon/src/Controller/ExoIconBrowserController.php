<?php

namespace Drupal\exo_icon\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\exo_icon\ExoIconRepositoryInterface;
use Drupal\exo_modal\Ajax\ExoModalOpenCommand;

/**
 * Class ExoIconBrowserController.
 */
class ExoIconBrowserController extends ControllerBase {

  /**
   * Drupal\exo_icon\ExoIconRepositoryInterface definition.
   *
   * @var \Drupal\exo_icon\ExoIconRepositoryInterface
   */
  protected $exoIconRepository;

  /**
   * Constructs a new ExoIconBrowserController object.
   */
  public function __construct(ExoIconRepositoryInterface $exo_icon_repository) {
    $this->exoIconRepository = $exo_icon_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('exo_icon.repository')
    );
  }

  /**
   * View dialog content.
   *
   * @param string $id
   *   The eXo modal item.
   * @param string $packages
   *   The eXo icon package IDs seperated with a +.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function view($id, $packages = '') {
    $build = [];

    // Modal ajax is included here to make sure it is loaded first.
    $build['#attached']['library'][] = 'exo_modal/ajax';
    $build['browser'] = [
      '#type' => 'exo_icon_browser',
      '#id' => $id,
      '#packages' => !empty($packages) ? explode(' ', $packages) : NULL,
      '#settings' => [
        'onBuild' => 'Drupal.ExoIconField.onBuild',
        'onSelect' => 'Drupal.ExoIconField.onSelect',
      ],
    ];

    $response = new AjaxResponse();
    $options = [
      'title' => $this->t('Icon Browser'),
      'subtitle' => $this->t('Click on an icon to select it.'),
      'icon' => 'regular-icons',
      'class' => 'exo-icon-browser-modal',
      'openFullscreen' => TRUE,
      'nest' => TRUE,
    ];

    $response->addCommand(new ExoModalOpenCommand($id, $build, $options));
    return $response;
  }

}
