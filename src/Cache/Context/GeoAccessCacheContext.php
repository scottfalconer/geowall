<?php

namespace Drupal\geowall\Cache\Context;

use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\geowall\GeoWallRestrictionChecker;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Cache context for geo access.
 */
final class GeoAccessCacheContext implements CacheContextInterface {

  /**
   * Restriction checker service.
   */
  protected GeoWallRestrictionChecker $checker;

  /**
   * Request stack service.
   */
  protected RequestStack $requestStack;

  public function __construct(GeoWallRestrictionChecker $checker, RequestStack $request_stack) {
    $this->checker = $checker;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel(): string {
    return t('Geo access');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext(): string {
    $request = $this->requestStack->getCurrentRequest();
    if (!$request) {
      return 'unknown';
    }
    return $this->checker->isRestricted($request) ? 'disallowed' : 'allowed';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata(): CacheableMetadata {
    return new CacheableMetadata();
  }
}
