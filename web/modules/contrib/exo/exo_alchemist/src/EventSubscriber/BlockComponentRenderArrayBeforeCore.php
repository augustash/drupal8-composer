<?php

namespace Drupal\exo_alchemist\EventSubscriber;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\exo_alchemist\Controller\ExoFieldParentsTrait;
use Drupal\exo_alchemist\ExoComponentManager;
use Drupal\layout_builder\Event\SectionComponentBuildRenderArrayEvent;
use Drupal\layout_builder\LayoutBuilderEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Builds render arrays and handles access for all block components.
 *
 * @internal
 *   Tagged services are internal.
 */
class BlockComponentRenderArrayBeforeCore implements EventSubscriberInterface {

  use StringTranslationTrait;
  use ExoFieldParentsTrait;

  /**
   * The eXo component plugin manager.
   *
   * @var \Drupal\exo_alchemist\ExoComponentManager
   */
  protected $exoComponentManager;

  /**
   * Constructs a new ExoComponentParamConverter object.
   *
   * @param \Drupal\exo_alchemist\ExoComponentManager $exo_component_manager
   *   The eXo component manager.
   */
  public function __construct(ExoComponentManager $exo_component_manager) {
    $this->exoComponentManager = $exo_component_manager;
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
    if (!empty($configuration['block_revision_id'])) {
      if (!empty($configuration['block_uuid'])) {
        /** @var \Drupal\block_content\BlockContentInterface $inline_block */
        $inline_block = $this->exoComponentManager->entityLoadByRevisionId($configuration['block_revision_id']);
        if (!$inline_block || $inline_block->uuid() !== $configuration['block_uuid']) {
          $inline_block = $this->exoComponentManager->entityLoadByUuid($configuration['block_uuid']);
          if ($inline_block) {
            $configuration['block_revision_id'] = $inline_block->getRevisionId();
          }
          $block->setConfiguration($configuration);
        }
      }
      else {
        // There are instances where a block revision may no longer exist.
        // Layout builder will white screen when this happens. In order to avoid
        // this, we check to make sure it exists and if it does not, we unset it.
        $inline_block = $this->exoComponentManager->entityLoadByRevisionId($configuration['block_revision_id']);
        if (empty($inline_block)) {
          $configuration['block_revision_id'] = NULL;
        }
        $block->setConfiguration($configuration);
      }
    }

  }

}
