<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarBadgeType;

use Drupal\exo_toolbar\Plugin\ExoToolbarBadgeTypeBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_toolbar\ExoToolbarElementInterface;
use Drupal\exo_toolbar\Plugin\ExoToolbarItemPluginInterface;
use Drupal\Core\Cache\Cache;

/**
 * Plugin implementation of the 'hook' eXo toolbar badge type.
 *
 * @ExoToolbarBadgeType(
 *   id = "hook",
 *   label = @Translation("Hook"),
 * )
 */
class Hook extends ExoToolbarBadgeTypeBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'cache_tags' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function badgeTypeForm(array $form, FormStateInterface $form_state) {
    $form = parent::badgeTypeForm($form, $form_state);
    $form['notice'] = [
      '#markup' => $this->t('This plugin will call the following hooks to find its value: <ul><li><code>@hook</code></li><li><code>@hook2</code></li><li><code>@hook3</code></li></ul>', [
        '@hook' => 'hook_exo_toolbar_badge_alter(&$badge, $type, $context)',
        '@hook2' => 'hook_exo_toolbar_badge_type_[TOOLBAR_ITEM_TYPE]_alter(&$badge, $type, $context)',
        '@hook2' => 'hook_exo_toolbar_badge_item_[TOOLBAR_ITEM_ID]_alter(&$badge, $type, $context)',
        '@hook3' => 'hook_exo_toolbar_badge_item_[TOOLBAR_ITEM_ID]_[KEY]_alter(&$badge, $type, $context)',
      ]),
    ];
    $form['cache_tags'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Cache Tags'),
      '#description' => $this->t('The badge will be cached by default for 5 minutes. Entering cache tags will remove this expiration and use the supplied cache tags for invalidation. Enter one cache tag per line.'),
      '#default_value' => $this->configuration['cache_tags'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function elementPrepare(ExoToolbarElementInterface $element, $delta, ExoToolbarItemPluginInterface $item) {
    $item_id = $item->getConfiguration()['toolbar_item_id'];
    $type = $item->getPluginId();
    $badge = NULL;
    $context = [
      'type' => $type,
      'item_id' => $item_id,
      'delta' => $delta,
    ];
    \Drupal::moduleHandler()->alter([
      'exo_toolbar_badge',
      "exo_toolbar_badge_type_{$type}",
      "exo_toolbar_badge_item_{$item_id}",
      "exo_toolbar_badge_item_{$item_id}_{$delta}",
    ], $badge, $context);
    if (is_string($badge) || is_int($badge)) {
      parent::elementPrepare($element, $delta, $item);
      $element->setBadge($badge);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = ['config:exo_toolbar_item_list'];
    if (!empty($this->configuration['cache_tags'])) {
      $tags = Cache::mergeTags($tags, array_map('trim', explode("\n", $this->configuration['cache_tags'])));
    }
    return $tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    $max_age = parent::getCacheMaxAge();
    if (empty($this->configuration['cache_tags'])) {
      // Cache for 5 minutes if no tags have been set.
      $max_age = (5 * 60);
    }
    return $max_age;
  }

}
