<?php

namespace Drupal\exo_icon\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;

/**
 * Class ExoIconPackageRebuildForm.
 */
class ExoIconPackageRebuildForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'exo_icon_package_rebuild_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to rebuild base icon packages?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('The %packages will be rebuilt. This action cannot be undone.', [
      '%packages' => implode(', ', ['Brand', 'Regular', 'Solid', 'Thin', 'Duo']),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Rebuild');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.exo_icon_package.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $storage = \Drupal::service('entity_type.manager')->getStorage('exo_icon_package');
    $entities = $storage->loadMultiple([
      'brand',
      'regular',
      'solid',
      'thin',
      'dio',
    ]);
    $storage->delete($entities);

    // Re-import the default config for a module or profile, etc.
    \Drupal::service('config.installer')->installDefaultConfig('module', 'exo_icon');
    drupal_flush_all_caches();

    \Drupal::messenger()->addMessage($this->t('Base icons have been re-improted.'));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
