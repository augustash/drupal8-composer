<?php

namespace Drupal\exo_site_settings_domain\EventSubscriber;

use Drupal\domain\DomainNegotiatorInterface;
use Drupal\exo_site_settings\Event\SiteSettingsConfigCloneEvent;
use Drupal\exo_site_settings\Event\SiteSettingsPreloadEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SiteSettingsDomainSubscriber.
 *
 * @package Drupal\exo_site_settings_domain\EventSubscriber
 */
class SiteSettingsDomainSubscriber implements EventSubscriberInterface {

  /**
   * Domain negotiation.
   *
   * @var \Drupal\domain\DomainNegotiator
   */
  protected $domainNegotiator;

  protected $activeDomain;

  /**
   * Constructs a ViewsEntitySchemaSubscriber.
   *
   * @param \Drupal\domain\DomainNegotiatorInterface $domain_negotiator
   *   The domain negotiator.
   */
  public function __construct(DomainNegotiatorInterface $domain_negotiator) {
    $this->domainNegotiator = $domain_negotiator;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      SiteSettingsPreloadEvent::EVENT_NAME => 'onSiteSettingsPreload',
      SiteSettingsConfigCloneEvent::EVENT_NAME => 'onSiteSettingsConfigClone',
    ];
  }

  /**
   * Subscribe to the site settings preload event dispatched.
   *
   * @param \Drupal\exo_site_settings\Event\SiteSettingsPreloadEvent $event
   *   Dat event object yo.
   */
  public function onSiteSettingsPreload(SiteSettingsPreloadEvent $event) {
    $domain = $this->getActiveDomain();
    if ($domain && !$domain->isDefault()) {
      $event->setTypeId($event->getTypeId() . '_' . $domain->id());
    }
  }

  /**
   * Subscribe to the site settings config clone event dispatched.
   *
   * @param \Drupal\exo_site_settings\Event\SiteSettingsConfigCloneEvent $event
   *   Dat event object yo.
   */
  public function onSiteSettingsConfigClone(SiteSettingsConfigCloneEvent $event) {
    if ($event->isEmpty()) {
      return;
    }
    $domain = $this->getActiveDomain();
    if ($domain) {
      if ($event->getName() == 'system.site' && \Drupal::moduleHandler()->moduleExists('domain_site_settings')) {
        if ($event->getKey() == 'page.front') {
          $event->setKey('frontpage');
        }
        switch ($event->getKey()) {
          case 'page.front':
            $event->setKey('frontpage');
            break;

          case 'page.403':
            $event->setKey('403');
            break;

          case 'page.404':
            $event->setKey('404');
            break;
        }
        $event->setName('domain_site_settings.domainconfigsettings');
        $event->setKey($domain->id() . '.site_' . $event->getKey());
        return;
      }
      $event->setName('domain.config.' . $domain->id() . '.en.' . $event->getName());
    }
  }

  /**
   * Get the active domain.
   *
   * @return \Drupal\domain\Entity\DomainInterface
   *   The currently active domain.
   */
  protected function getActiveDomain() {
    if (!isset($this->activeDomain)) {
      $active = $this->domainNegotiator->getActiveDomain();
      if (empty($active)) {
        $active = $this->domainNegotiator->getActiveDomain(TRUE);
      }
      $this->activeDomain = $active;
    }
    return $this->activeDomain;
  }

}
