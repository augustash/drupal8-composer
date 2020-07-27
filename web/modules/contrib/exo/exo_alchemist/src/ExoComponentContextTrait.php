<?php

namespace Drupal\exo_alchemist;

/**
 * Class ExoComponentContextTrait.
 */
trait ExoComponentContextTrait {

  /**
   * {@inheritdoc}
   */
  public function isLayoutBuilder(array $contexts) {
    return !isset($contexts['entity']) && !$this->isPreview($contexts);
  }

  /**
   * {@inheritdoc}
   */
  public function isPreview(array $contexts) {
    return isset($contexts['preview']) ? $contexts['preview']->getContextValue() : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked(array $contexts) {
    return isset($contexts['exo_section_lock']) ? $contexts['exo_section_lock']->getContextValue() : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isDefaultStorage(array $contexts) {
    return isset($contexts['default_storage']) ? $contexts['default_storage']->getContextValue() : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isNestedStorage(array $contexts) {
    return isset($contexts['nested_storage']) ? $contexts['nested_storage']->getContextValue() : FALSE;
  }

}
