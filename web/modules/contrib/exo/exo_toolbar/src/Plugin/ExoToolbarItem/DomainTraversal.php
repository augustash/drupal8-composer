<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarItem;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\exo_toolbar\Entity\ExoToolbarItemInterface;
use Drupal\exo_toolbar\Plugin\ExoToolbarItemDialogBase;

/**
 * Plugin implementation of the 'admin_escape' eXo toolbar item.
 *
 * @ExoToolbarItem(
 *   id = "domain_traversal",
 *   admin_label = @Translation("Domain Traversal"),
 *   category = @Translation("Domain Access"),
 *   provider = "domain_traversal",
 * )
 */
class DomainTraversal extends ExoToolbarItemDialogBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'title' => 'Switch Domain',
      'icon' => 'regular-globe',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function dialogBuild(ExoToolbarItemInterface $exo_toolbar_item, $arg = NULL) {
    $build = [
      '#theme' => 'links',
      '#links' => [],
      '#attributes' => ['class' => ['exo-toolbar-element-list']],
    ];

    $cacheable_metadata = CacheableMetadata::createFromRenderArray($build);

    /** @var \Drupal\domain\Entity\Domain[] $domains */
    $domains = $this->entityTypeManager()->getStorage('domain')->loadMultiple();
    foreach ($domains as $domain) {
      if (!$domain->status()) {
        continue;
      }

      $url = new Url('domain_traversal.traverse', [
        'domain' => $domain->id(),
      ]);
      if ($url->access()) {
        $build['#links'][$domain->id()] = [
          'title' => $domain->get('name'),
          'url' => $url,
        ];
      }
      $cacheable_metadata->addCacheableDependency($domain);
    }
    $cacheable_metadata->applyTo($build);
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function itemAccess(AccountInterface $account) {

    /** @var \Drupal\domain\Entity\Domain[] $domains */
    $domains = $this->entityTypeManager()->getStorage('domain')->loadMultiple();
    foreach ($domains as $domain) {
      if ($domain->status()) {
        return AccessResult::allowed()->cachePerPermissions();
      }
    }

    // No opinion.
    return AccessResult::neutral();
  }

}
