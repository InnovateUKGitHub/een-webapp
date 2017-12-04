<?php

namespace Drupal\image_widget_crop\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\crop\Entity\CropType;
use Drupal\image_widget_crop\ImageWidgetCropManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure ImageWidgetCrop general settings for this site.
 */
class CropWidgetForm extends ConfigFormBase {

  /**
   * The settings of image_widget_crop configuration.
   *
   * @var array
   *
   * @see \Drupal\Core\Config\Config
   */
  protected $settings;

  /**
   * Instance of API ImageWidgetCropManager.
   *
   * @var \Drupal\image_widget_crop\ImageWidgetCropManager
   */
  protected $imageWidgetCropManager;

  /**
   * Constructs a CropWidgetForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ImageWidgetCropManager $iwc_manager) {
    parent::__construct($config_factory);
    $this->settings = $this->config('image_widget_crop.settings');
    $this->imageWidgetCropManager = $iwc_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('config.factory'),
      $container->get('image_widget_crop.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'image_widget_crop_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['image_widget_crop.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $url = 'https://cdnjs.com/libraries/cropper';
    $cdn_js = 'https://cdnjs.cloudflare.com/ajax/libs/cropper/2.3.4/cropper.min.js';
    $cdn_css = 'https://cdnjs.cloudflare.com/ajax/libs/cropper/2.3.4/cropper.min.css';

    $form['library'] = [
      '#type' => 'details',
      '#title' => $this->t('Cropper library settings'),
      '#description' => $this->t('Changes here require a cache rebuild to take full effect.'),
    ];

    $form['library']['library_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom Cropper library'),
      '#description' => $this->t('Set the URL or local path for the file, or leave empty to use the installed library (if present) or a <a href="@file">CDN</a> fallback. You can retrieve the library file from <a href="@url">Cropper CDN</a>.', [
        '@url' => $url,
        '@file' => $cdn_js,
      ]),
      '#default_value' => $this->settings->get('settings.library_url'),
    ];

    $form['library']['css_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom Cropper CSS file'),
      '#description' => $this->t('Set the URL or local path for the file, or leave empty to use installed library (if installed) or a <a href="@file">CDN</a> fallback. You can retrieve the CSS file from <a href="@url">Cropper CDN</a>.', [
        '@url' => $url,
        '@file' => $cdn_css,
      ]),
      '#default_value' => $this->settings->get('settings.css_url'),
    ];

    // Indicate which files are used when custom urls are not set.
    if (\Drupal::moduleHandler()->moduleExists('libraries')
      && ($info = libraries_detect('cropper')) && $info['installed']) {
      $form['library']['library_url']['#attributes']['placeholder'] = $info['library path'] . '/dist/' . key($info['files']['js']);
      $form['library']['css_url']['#attributes']['placeholder'] = $info['library path'] . '/dist/' . key($info['files']['css']);
    }
    else {
      $form['library']['library_url']['#attributes']['placeholder'] = $cdn_js;
      $form['library']['css_url']['#attributes']['placeholder'] = $cdn_css;
    }

    $form['image_crop'] = [
      '#type' => 'details',
      '#title' => t('General configuration'),
    ];

    $form['image_crop']['crop_preview_image_style'] = [
      '#title' => $this->t('Crop preview image style'),
      '#type' => 'select',
      '#options' => $this->imageWidgetCropManager->getAvailableCropImageStyle(image_style_options(FALSE)),
      '#default_value' => $this->settings->get('settings.crop_preview_image_style'),
      '#description' => $this->t('The preview image will be shown while editing the content.'),
      '#weight' => 15,
    ];

    $form['image_crop']['crop_list'] = [
      '#title' => $this->t('Crop Type'),
      '#type' => 'select',
      '#options' => $this->imageWidgetCropManager->getAvailableCropType(CropType::getCropTypeNames()),
      '#empty_option' => $this->t('<@no-preview>', ['@no-preview' => $this->t('no preview')]),
      '#default_value' => $this->settings->get('settings.crop_list'),
      '#multiple' => TRUE,
      '#description' => $this->t('The type of crop to apply to your image. If your Crop Type not appear here, set an image style use your Crop Type'),
      '#weight' => 16,
    ];

    $form['image_crop']['show_crop_area'] = [
      '#title' => $this->t('Always expand crop area'),
      '#type' => 'checkbox',
      '#default_value' => $this->settings->get('settings.show_crop_area'),
    ];

    $form['image_crop']['warn_multiple_usages'] = [
      '#title' => $this->t('Warn user when a file have multiple usages'),
      '#type' => 'checkbox',
      '#default_value' => $this->settings->get('settings.warn_multiple_usages'),
    ];

    $form['image_crop']['show_default_crop'] = [
      '#title' => $this->t('Show default crop area'),
      '#type' => 'checkbox',
      '#default_value' => $this->settings->get('settings.show_default_crop'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Validation for cropper library.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if (!empty($form_state->getValue('library_url')) || !empty($form_state->getValue('css_url'))) {
      $files = [
        'library' => $form_state->getValue('library_url'),
        'css' => $form_state->getValue('css_url'),
      ];
      if (empty($files['library']) || empty($files['css'])) {
        $form_state->setErrorByName('plugin', t('Please provide both a library and a CSS file when using custom URLs.'));
      }
      else {
        foreach ($files as $type => $file) {
          // Verify that both files exist.
          $is_local = parse_url($file, PHP_URL_SCHEME) === NULL && strpos($file, '//') !== 0;
          if ($is_local && !file_exists($file)) {
            $form_state->setErrorByName($type . '_url', t('The provided local file does not exist.'));
          }
          elseif (!$is_local) {
            try {
              $result = \Drupal::httpClient()->request('GET', $file);
              if ($result->getStatusCode() != 200) {
                throw new \Exception($result->getReasonPhrase(), 1);
              }
            }
            catch (\Exception $e) {
              $form_state->setErrorByName($type . '_url', t('The remote URL for the library does not appear to be valid: @message.', [
                '@message' => $e->getMessage(),
              ]), 'error');
            }
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    // We need to rebuild the library cache if we switch from remote to local
    // library or vice-versa.
    Cache::invalidateTags(['library_info']);

    $this->settings
      ->set("settings.library_url", $form_state->getValue('library_url'))
      ->set("settings.css_url", $form_state->getValue('css_url'))
      ->set("settings.crop_preview_image_style", $form_state->getValue('crop_preview_image_style'))
      ->set("settings.show_default_crop", $form_state->getValue('show_default_crop'))
      ->set("settings.show_crop_area", $form_state->getValue('show_crop_area'))
      ->set("settings.warn_multiple_usages", $form_state->getValue('warn_multiple_usages'))
      ->set("settings.crop_list", $form_state->getValue('crop_list'));
    $this->settings->save();
  }

}