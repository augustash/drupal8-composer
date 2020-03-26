<?php

namespace Drupal\exo_icon\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ExoIconPackageForm.
 */
class ExoIconPackageForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\exo_icon\Entity\ExoIconPackageInterface $exo_icon_package */
    $exo_icon_package = $this->entity;

    if ($exo_icon_package->isNew()) {
      $form['#title'] = $this->t('Add Icon Package');
    }

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $exo_icon_package->label(),
      '#description' => $this->t("Label for the eXo Icon Package."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Class prefix'),
      '#description' => $this->t('The unique selector prefix of this package. It will be used for rendering the icons within class names and paths. It will replace any class prefix or font names specified within the IcoMoon zip package.'),
      '#default_value' => $exo_icon_package->id(),
      '#field_prefix' => '.',
      '#machine_name' => [
        'exists' => '\Drupal\exo_icon\Entity\ExoIconPackage::load',
        'replace_pattern' => '[^a-z0-9-]+',
        'replace' => '-',
        'field_prefix' => '.',
      ],
      '#disabled' => !$exo_icon_package->isNew(),
    ];

    $form['path'] = [
      '#type' => 'exo_config_file',
      '#title' => $exo_icon_package->isNew() ? $this->t('IcoMoon Font Package') : $this->t('Replace IcoMoon Font Package'),
      '#description' => $this->t('An IcoMoon font package. <a href="https://icomoon.io">Generate & Download</a>'),
      '#extensions' => ['zip'],
      '#required' => $exo_icon_package->isNew(),
      '#file_name' => '[exo_config_file:id]',
      '#default_value' => $exo_icon_package->getPath(),
    ];

    /** @var \Drupal\exo_icon\ExoIconRepository $exo_icon_repository */
    $exo_icon_repository = \Drupal::service('exo_icon.repository');
    $global_packages = $exo_icon_repository->getPackagesByGlobal();
    $system_package_ids = $exo_icon_repository->getSystemPackageIds();

    $form['global'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Global'),
      '#description' => $this->t('If checked, this icon package will be included on each page. This is useful when using icons via CSS.'),
      '#default_value' => $exo_icon_package->isGlobal(),
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Published'),
      '#description' => $this->t('If checked, this icon package will be available for selection within Drupal.'),
      '#default_value' => $exo_icon_package->status(),
      '#disabled' => count($global_packages) === 1 && isset($global_packages[$exo_icon_package->id()]) && in_array($exo_icon_package->id(), $system_package_ids),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $exo_icon_package = $this->entity;
    $status = $exo_icon_package->save();

    switch ($status) {
      case SAVED_NEW:
        \Drupal::messenger()->addMessage($this->t('Created the %label eXo Icon Package.', [
          '%label' => $exo_icon_package->label(),
        ]));
        break;

      default:
        \Drupal::messenger()->addMessage($this->t('Saved the %label eXo Icon Package.', [
          '%label' => $exo_icon_package->label(),
        ]));
    }

    drupal_flush_all_caches();
    $form_state->setRedirectUrl($exo_icon_package->toUrl('collection'));
  }

}
