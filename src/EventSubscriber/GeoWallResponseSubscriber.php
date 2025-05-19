<?php

namespace Drupal\geowall\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Drupal\geowall\GeoWallRestrictionChecker;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Cache\CacheableResponseInterface;

/**
 * Adds cache context and Vary header on responses.
 */
final class GeoWallResponseSubscriber implements EventSubscriberInterface {

  /**
   * Restriction checker service.
   */
  protected GeoWallRestrictionChecker $checker;

  /**
   * Config factory.
   */
  protected ConfigFactoryInterface $configFactory;

  public function __construct(GeoWallRestrictionChecker $checker, ConfigFactoryInterface $config_factory) {
    $this->checker = $checker;
    $this->configFactory = $config_factory;
  }

  public static function getSubscribedEvents(): array {
    $events[KernelEvents::RESPONSE][] = ['onResponse'];
    return $events;
  }

  public function onResponse(ResponseEvent $event): void {
    $config = $this->configFactory->get('geowall.settings');
    if (!$config->get('enable_geowall')) {
      return;
    }
    $request = $event->getRequest();
    $response = $event->getResponse();

    if ($response instanceof CacheableResponseInterface && $response->headers->get('content-type') && strpos($response->headers->get('content-type'), 'text/html') === 0) {
      if ($this->checker->isPathPotentiallyRestricted($request)) {
        $response->headers->set('Vary', 'CF-IPCountry', FALSE);
        $metadata = $response->getCacheableMetadata();
        $metadata->addCacheContexts(['geowall.geo_access']);
      }
    }
  }

}
