<?php

namespace Drupal\geowall\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure GeoWall settings.
 */
final class GeoWallSettingsForm extends ConfigFormBase {

  /**
   * Path validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected PathValidatorInterface $pathValidator;

  /**
   * Path matcher service.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected PathMatcherInterface $pathMatcher;

  /**
   * Constructs the settings form.
   */
  public function __construct(ConfigFactoryInterface $config_factory, PathValidatorInterface $path_validator, PathMatcherInterface $path_matcher) {
    parent::__construct($config_factory);
    $this->pathValidator = $path_validator;
    $this->pathMatcher = $path_matcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('config.factory'),
      $container->get('path.validator'),
      $container->get('path.matcher')
    );
  }

  /** @inheritdoc */
  protected function getEditableConfigNames(): array {
    return ['geowall.settings'];
  }

  /** @inheritdoc */
  public function getFormId(): string {
    return 'geowall_settings_form';
  }

  /** @inheritdoc */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('geowall.settings');

    $form['enable_geowall'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Geowall'),
      '#default_value' => $config->get('enable_geowall'),
    ];

    $form['allowed_countries'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Allowed countries'),
      '#default_value' => implode("\n", $config->get('allowed_countries') ?: ['US']),
      '#description' => $this->t('One ISO country code per line.'),
    ];

    $form['restricted_content_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Restricted content types'),
      '#options' => node_type_get_names(),
      '#default_value' => $config->get('restricted_content_types') ?: [],
    ];

    $form['restricted_paths'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Restricted paths'),
      '#default_value' => implode("\n", $config->get('restricted_paths') ?: []),
      '#description' => $this->t('Enter one path pattern per line.'),
    ];

    $form['excluded_paths'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Excluded paths'),
      '#default_value' => implode("\n", $config->get('excluded_paths') ?: ['/admin/*','/user/*','/system/ajax']),
      '#description' => $this->t('Paths that should never be restricted.'),
    ];

    $form['redirect_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Redirect path'),
      '#default_value' => $config->get('redirect_path'),
    ];

    $form['default_action_if_no_header'] = [
      '#type' => 'select',
      '#title' => $this->t('Default action if no header'),
      '#options' => ['allow' => $this->t('Allow'), 'block' => $this->t('Block')],
      '#default_value' => $config->get('default_action_if_no_header'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /** {@inheritdoc} */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $redirect = '/' . ltrim($form_state->getValue('redirect_path'), '/');
    if (!$this->pathValidator->isValid($redirect)) {
      $form_state->setErrorByName('redirect_path', $this->t('The redirect path must be a valid internal path.'));
    }
    foreach (array_merge(
      array_filter(array_map('trim', explode("\n", $form_state->getValue('restricted_paths')))),
      array_filter(array_map('trim', explode("\n", $form_state->getValue('excluded_paths'))))
    ) as $pattern) {
      if ($this->pathMatcher->matchPath($redirect, $pattern)) {
        $form_state->setErrorByName('redirect_path', $this->t('The redirect path cannot be a restricted or excluded path.'));
        break;
      }
    }

    $countries = array_filter(array_map('trim', explode("\n", $form_state->getValue('allowed_countries'))));
    foreach ($countries as $code) {
      if (!preg_match('/^[A-Z]{2}$/', $code)) {
        $form_state->setErrorByName('allowed_countries', $this->t('Country codes must be two uppercase letters.'));
        break;
      }
    }

    parent::validateForm($form, $form_state);
  }

  /** @inheritdoc */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('geowall.settings')
      ->set('enable_geowall', $form_state->getValue('enable_geowall'))
      ->set('allowed_countries', array_filter(array_map('trim', explode("\n", $form_state->getValue('allowed_countries')))))
      ->set('restricted_content_types', array_filter($form_state->getValue('restricted_content_types')))
      ->set('restricted_paths', array_filter(array_map('trim', explode("\n", $form_state->getValue('restricted_paths')))))
      ->set('excluded_paths', array_filter(array_map('trim', explode("\n", $form_state->getValue('excluded_paths')))))
      ->set('redirect_path', $form_state->getValue('redirect_path'))
      ->set('default_action_if_no_header', $form_state->getValue('default_action_if_no_header'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
