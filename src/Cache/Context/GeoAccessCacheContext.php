<?php

namespace Drupal\geowall\Cache\Context;

use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\geowall\GeoWallRestrictionChecker;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Cache context for geo access.
 */
class GeoAccessCacheContext implements CacheContextInterface {

  protected $checker;
  protected $requestStack;

  public function __construct(GeoWallRestrictionChecker $checker, RequestStack $request_stack) {
    $this->checker = $checker;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Geo access');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    $request = $this->requestStack->getCurrentRequest();
    if (!$request) {
      return 'unknown';
    }
    return $this->checker->isRestricted($request) ? 'disallowed' : 'allowed';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }
}
