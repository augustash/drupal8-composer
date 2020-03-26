<?php

namespace Drupal\exo_config_file;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Link;

/**
 * Provides a listing of eXo Config File entities.
 */
class ExoConfigFileListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['preview'] = '';
    $header['label'] = $this->t('ID');
    $header['path'] = $this->t('Path');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $parent = $entity->getParentEntity();
    $label = $this->t('<strong>@parent_type: @parent_label</strong> <small>(@machine)</small>', [
      '@parent_type' => $parent->getEntityType()->getLabel(),
      '@parent_label' => $parent->label(),
      '@machine' => $entity->id(),
    ]);
    if ($parent->hasLinkTemplate('canonical')) {
      $label = Link::fromTextAndUrl($label, $parent->toUrl('canonical'));
    }
    $row['preview'] = [
      'style' => 'width:1%;',
    ];
    if ($entity->isImage()) {
      $row['preview'] = [
        'style' => 'min-width:60px; width:1%;',
        'data' => [
          '#type' => 'inline_template',
          '#template' => '<img src="{{ image }}" style="width:60px; height: auto; background:#95999a;" />',
          '#context' => [
            'image' => file_create_url($entity->getFilePath()),
          ],
        ],
      ];
    }
    $row['label'] = $label;
    $row['path'] = $entity->getFilePath();
    return $row + parent::buildRow($entity);
  }

}
