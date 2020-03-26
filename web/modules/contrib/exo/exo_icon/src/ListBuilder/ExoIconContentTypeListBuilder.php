<?php

namespace Drupal\exo_icon\ListBuilder;

use Drupal\node\NodeTypeListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * Provides a listing of ContentType.
 */
class ExoIconContentTypeListBuilder extends NodeTypeListBuilder {
  use ExoIconTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['icon'] = t('Icon');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $icon = exo_icon_entity_icon($entity);
    $row['icon']['data']['#markup'] = $icon ? $this->icon()->setIcon($icon) : '';
    return $row + parent::buildRow($entity);
  }

}
