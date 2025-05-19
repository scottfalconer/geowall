<?php

namespace Drupal\geowall;

use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\node\NodeInterface;

/**
 * Contains logic to check if a request should be restricted.
 */
final class GeoWallRestrictionChecker {

  /**
   * GeoWall configuration object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected Config $config;

  /**
   * Path matcher service.
   */
  protected PathMatcherInterface $pathMatcher;

  /**
   * Current route match service.
   */
  protected CurrentRouteMatch $currentRouteMatch;

  /**
   * Request stack service.
   */
  protected RequestStack $requestStack;

  public function __construct(ConfigFactoryInterface $config_factory, PathMatcherInterface $path_matcher, CurrentRouteMatch $current_route_match, RequestStack $request_stack) {
    $this->config = $config_factory->get('geowall.settings');
    $this->pathMatcher = $path_matcher;
    $this->currentRouteMatch = $current_route_match;
    $this->requestStack = $request_stack;
  }

  /**
   * Gets visitor country code from the request.
   */
  public function getUserCountry(Request $request): ?string {
    return $request->headers->get('CF-IPCountry');
  }

  /**
   * Determines if current request is restricted.
   */
  public function isRestricted(Request $request): bool {
    if (!$this->isPathPotentiallyRestricted($request)) {
      return FALSE;
    }
    $country = $this->getUserCountry($request);
    $allowed = $this->config->get('allowed_countries') ?: ['US'];
    if ($country === NULL) {
      return $this->config->get('default_action_if_no_header') === 'block';
    }
    return !in_array($country, $allowed);
  }

  /**
   * Checks if path or content type might be restricted.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return bool
   *   TRUE if the path is potentially restricted.
   */
  public function isPathPotentiallyRestricted(Request $request): bool {
    $path = '/' . trim($request->getPathInfo(), '/');
    foreach ((array) $this->config->get('excluded_paths') as $pattern) {
      if ($this->pathMatcher->matchPath($path, $pattern)) {
        return FALSE;
      }
    }
    foreach ((array) $this->config->get('restricted_paths') as $pattern) {
      if ($this->pathMatcher->matchPath($path, $pattern)) {
        return TRUE;
      }
    }
    $node = $this->currentRouteMatch->getParameter('node');
    if ($node instanceof NodeInterface) {
      $restricted_types = (array) $this->config->get('restricted_content_types');
      if (in_array($node->bundle(), $restricted_types, TRUE)) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
