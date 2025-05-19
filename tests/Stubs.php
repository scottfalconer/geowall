<?php
namespace Drupal\Core\Config;
interface ConfigFactoryInterface { public function get(string $name); }
class Config {
    protected array $data;
    public function __construct(array $data = []) { $this->data = $data; }
    public function get(string $name) { return $this->data[$name] ?? null; }
}
class ConfigFactory implements ConfigFactoryInterface {
    protected Config $config;
    public function __construct(Config $config) { $this->config = $config; }
    public function get(string $name) { return $this->config; }
}

namespace Drupal\Core\Path;
interface PathMatcherInterface { public function matchPath(string $path, string $pattern): bool; }
interface PathValidatorInterface { public function isValid(string $path): bool; }
class PathMatcher implements PathMatcherInterface {
    private array $matches;
    public function __construct(array $matches = []) { $this->matches = $matches; }
    public function matchPath(string $path, string $pattern): bool {
        return $this->matches[$pattern] ?? false;
    }
}
class PathValidator implements PathValidatorInterface {
    private bool $valid;
    public function __construct(bool $valid) { $this->valid = $valid; }
    public function isValid(string $path): bool { return $this->valid; }
}

namespace Drupal\Core\Routing;
class CurrentRouteMatch {
    private $node;
    public function __construct($node = null) { $this->node = $node; }
    public function getParameter(string $name) { return $name === 'node' ? $this->node : null; }
}

namespace Drupal\node;
interface NodeInterface { public function bundle(); }
class Node implements NodeInterface {
    private string $bundle;
    public function __construct(string $bundle) { $this->bundle = $bundle; }
    public function bundle() { return $this->bundle; }
}

namespace Symfony\Component\HttpFoundation;
class HeaderBag {
    private array $headers;
    public function __construct(array $headers = []) { $this->headers = $headers; }
    public function get(string $name) { return $this->headers[$name] ?? null; }
}
class Request {
    public HeaderBag $headers;
    private string $pathInfo;
    public function __construct(string $path = '/', array $headers = []) {
        $this->pathInfo = $path;
        $this->headers = new HeaderBag($headers);
    }
    public function getPathInfo() { return $this->pathInfo; }
}
class RequestStack {
    private ?Request $current;
    public function __construct(?Request $request = null) { $this->current = $request; }
    public function getCurrentRequest() { return $this->current; }
}

namespace Drupal\Core\Form;
interface FormStateInterface { public function getValue(string $name); public function setErrorByName(string $name, string $message); }
class FormState implements FormStateInterface {
    private array $values;
    public array $errors = [];
    public function __construct(array $values = []) { $this->values = $values; }
    public function getValue(string $name) { return $this->values[$name] ?? null; }
    public function setValue(string $name, $value) { $this->values[$name] = $value; }
    public function setErrorByName(string $name, string $message) { $this->errors[$name] = $message; }
}
class ConfigFormBase {
    protected \Drupal\Core\Config\ConfigFactoryInterface $configFactory;
    public function __construct(\Drupal\Core\Config\ConfigFactoryInterface $config_factory) { $this->configFactory = $config_factory; }
    protected function config(string $name) { return $this->configFactory->get($name); }
    public function t(string $string) { return $string; }
    protected function getEditableConfigNames(): array { return []; }
    public function getFormId(): string { return ''; }
    public function validateForm(array &$form, FormStateInterface $form_state): void {}
    public function submitForm(array &$form, FormStateInterface $form_state): void {}
}
