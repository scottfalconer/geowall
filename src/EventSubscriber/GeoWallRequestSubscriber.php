<?php

namespace Drupal\geowall\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\geowall\GeoWallRestrictionChecker;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Redirects disallowed visitors before controller execution.
 */
final class GeoWallRequestSubscriber implements EventSubscriberInterface {

  /**
   * Restriction checker service.
   */
  protected GeoWallRestrictionChecker $checker;

  /**
   * Config factory.
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Constructs the subscriber.
   */
  public function __construct(GeoWallRestrictionChecker $checker, ConfigFactoryInterface $config_factory) {
    $this->checker = $checker;
    $this->configFactory = $config_factory;
  }

  /** @inheritdoc */
  public static function getSubscribedEvents(): array {
    $events[KernelEvents::REQUEST][] = ['onRequest', 30];
    return $events;
  }

  /**
   * Checks if request should be redirected.
   */
  public function onRequest(RequestEvent $event): void {
    $config = $this->configFactory->get('geowall.settings');
    if (!$config->get('enable_geowall')) {
      return;
    }

    $request = $event->getRequest();
    if ($this->checker->isRestricted($request)) {
      $target = '/' . ltrim($config->get('redirect_path'), '/');
      $response = new RedirectResponse($target);
      $response->headers->set('Vary', 'CF-IPCountry', FALSE);
      $event->setResponse($response);
    }
  }

}
