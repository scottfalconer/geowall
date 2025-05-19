<?php
require_once __DIR__ . '/../Stubs.php';
require_once __DIR__ . '/../../src/GeoWallRestrictionChecker.php';

use Drupal\geowall\GeoWallRestrictionChecker;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Path\PathMatcher;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\node\Node;
use PHPUnit\Framework\TestCase;

class GeoWallRestrictionCheckerTest extends TestCase {

    public function testAllowedCountryNotRestricted(): void {
        $config = new Config([
            'allowed_countries' => ['US'],
            'restricted_paths' => ['/foo'],
        ]);
        $factory = new ConfigFactory($config);
        $matcher = new PathMatcher(['/foo' => true]);
        $route = new CurrentRouteMatch();
        $stack = new RequestStack();
        $checker = new GeoWallRestrictionChecker($factory, $matcher, $route, $stack);

        $request = new Request('/foo', ['CF-IPCountry' => 'US']);
        $this->assertFalse($checker->isRestricted($request));
    }

    public function testDisallowedCountryRestricted(): void {
        $config = new Config([
            'allowed_countries' => ['US'],
            'restricted_paths' => ['/foo'],
        ]);
        $checker = new GeoWallRestrictionChecker(
            new ConfigFactory($config),
            new PathMatcher(['/foo' => true]),
            new CurrentRouteMatch(),
            new RequestStack()
        );

        $request = new Request('/foo', ['CF-IPCountry' => 'CA']);
        $this->assertTrue($checker->isRestricted($request));
    }

    public function testDefaultActionIfNoHeaderBlock(): void {
        $config = new Config([
            'allowed_countries' => ['US'],
            'restricted_paths' => ['/foo'],
            'default_action_if_no_header' => 'block',
        ]);
        $checker = new GeoWallRestrictionChecker(
            new ConfigFactory($config),
            new PathMatcher(['/foo' => true]),
            new CurrentRouteMatch(),
            new RequestStack()
        );

        $request = new Request('/foo');
        $this->assertTrue($checker->isRestricted($request));
    }

    public function testRestrictedByContentType(): void {
        $config = new Config([
            'allowed_countries' => ['US'],
            'restricted_content_types' => ['page'],
        ]);
        $node = new Node('page');
        $checker = new GeoWallRestrictionChecker(
            new ConfigFactory($config),
            new PathMatcher([]),
            new CurrentRouteMatch($node),
            new RequestStack()
        );

        $request = new Request('/node/1', ['CF-IPCountry' => 'CA']);
        $this->assertTrue($checker->isRestricted($request));
    }
}
