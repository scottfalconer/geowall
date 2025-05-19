<?php
require_once __DIR__ . '/../Stubs.php';
require_once __DIR__ . '/../../src/Form/GeoWallSettingsForm.php';

use Drupal\geowall\Form\GeoWallSettingsForm;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Path\PathValidator;
use Drupal\Core\Path\PathMatcher;
use Drupal\Core\Form\FormState;
use PHPUnit\Framework\TestCase;

class GeoWallSettingsFormTest extends TestCase {

    private function getForm(bool $path_valid = true, array $match = []): GeoWallSettingsForm {
        $config = new Config([]);
        $factory = new ConfigFactory($config);
        return new GeoWallSettingsForm($factory, new PathValidator($path_valid), new PathMatcher($match));
    }

    public function testInvalidRedirectPath(): void {
        $form = $this->getForm(false);
        $state = new FormState([
            'redirect_path' => '/invalid',
            'restricted_paths' => '',
            'excluded_paths' => '',
            'allowed_countries' => "US",
        ]);
        $form->validateForm([], $state);
        $this->assertArrayHasKey('redirect_path', $state->errors);
    }

    public function testRedirectPathRestricted(): void {
        $form = $this->getForm(true, ['/foo' => true]);
        $state = new FormState([
            'redirect_path' => '/foo',
            'restricted_paths' => "/foo",
            'excluded_paths' => '',
            'allowed_countries' => "US",
        ]);
        $form->validateForm([], $state);
        $this->assertArrayHasKey('redirect_path', $state->errors);
    }

    public function testAllowedCountriesValidation(): void {
        $form = $this->getForm();
        $state = new FormState([
            'redirect_path' => '/ok',
            'restricted_paths' => '',
            'excluded_paths' => '',
            'allowed_countries' => "US\nbad",
        ]);
        $form->validateForm([], $state);
        $this->assertArrayHasKey('allowed_countries', $state->errors);
    }
}
