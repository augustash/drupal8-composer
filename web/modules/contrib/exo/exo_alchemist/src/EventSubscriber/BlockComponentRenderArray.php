<?php

namespace Drupal\exo_alchemist\EventSubscriber;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\exo_alchemist\Controller\ExoFieldParentsTrait;
use Drupal\layout_builder\Event\SectionComponentBuildRenderArrayEvent;
use Drupal\layout_builder\LayoutBuilderEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Builds render arrays and handles access for all block components.
 *
 * @internal
 *   Tagged services are internal.
 */
class BlockComponentRenderArray implements EventSubscriberInterface {

  use StringTranslationTrait;
  use ExoFieldParentsTrait;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Creates a BlockComponentRenderArray object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The current user.
   */
  public function __construct(EntityRepositoryInterface $entity_repository) {
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[LayoutBuilderEvents::SECTION_COMPONENT_BUILD_RENDER_ARRAY] = ['onBuildRender', 102];
    return $events;
  }

  /**
   * Builds render arrays for block plugins and sets it on the event.
   *
   * @param \Drupal\layout_builder\Event\SectionComponentBuildRenderArrayEvent $event
   *   The section component render event.
   */
  public function onBuildRender(SectionComponentBuildRenderArrayEvent $event) {
    $block = $event->getPlugin();
    if (!$block instanceof BlockPluginInterface) {
      return;
    }

    $configuration = $block->getConfiguration();
    if (!empty($configuration['block_uuid'])) {
      /** @var \Drupal\block_content\BlockContentInterface $inline_block */
      $inline_block = $this->entityRepository->loadEntityByUuid('block_content', $configuration['block_uuid']);
      if ($inline_block) {
        $configuration['block_revision_id'] = $inline_block->getRevisionId();
      }
      $block->setConfiguration($configuration);
    }

  }

}
