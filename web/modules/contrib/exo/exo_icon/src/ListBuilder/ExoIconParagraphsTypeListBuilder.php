<?php

namespace Drupal\exo_icon\ListBuilder;

use Drupal\paragraphs\Controller\ParagraphsTypeListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * Provides a listing of ParagraphsType.
 */
class ExoIconParagraphsTypeListBuilder extends ParagraphsTypeListBuilder {
  use ExoIconTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = parent::buildRow($entity);
    if (isset($row['icon_file'])) {
      $icon = exo_icon_entity_icon($entity);
      if ($icon) {
        $row['icon_file'] = [];
        $row['icon_file']['data']['#markup'] = $this->icon()->setIcon($icon);
      }
    }
    return $row;
  }

}
