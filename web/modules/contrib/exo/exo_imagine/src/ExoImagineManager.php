<?php

namespace Drupal\exo_imagine;

use Drupal\breakpoint\BreakpointManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\exo\ExoSettingsInterface;
use Drupal\file\FileInterface;
use Drupal\image\ImageEffectManager;

/**
 * Class ExoImagineManager.
 */
class ExoImagineManager {

  /**
   * Component prefix.
   */
  const PREVIEW_BLUR_QUALITY = 75;

  /**
   * Drupal\Core\Extension\ModuleHandlerInterface definition.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The breakpoint manager.
   *
   * @var \Drupal\breakpoint\BreakpointManagerInterface
   */
  protected $breakpointManager;

  /**
   * Drupal\Core\Image\ImageFactory definition.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * Drupal\image\ImageEffectManager definition.
   *
   * @var \Drupal\image\ImageEffectManager
   */
  protected $imageEffectManager;

  /**
   * The exo imagine settings.
   *
   * @var \Drupal\exo\ExoSettingsInterface
   */
  protected $exoImagineSettings;

  /**
   * Constructs a new ExoImagineManager object.
   */
  public function __construct(ModuleHandlerInterface $module_handler, EntityTypeManagerInterface $entity_type_manager, BreakpointManagerInterface $breakpoint_manager, ImageFactory $image_factory, ImageEffectManager $plugin_manager_image_effect, ExoSettingsInterface $exo_imagine_settings) {
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->breakpointManager = $breakpoint_manager;
    $this->imageFactory = $image_factory;
    $this->imageEffectManager = $plugin_manager_image_effect;
    $this->exoImagineSettings = $exo_imagine_settings;
  }

  /**
   * Get image definition.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file entity that contains the original uri.
   * @param int $width
   *   The image width.
   * @param int $height
   *   The image height.
   * @param string $unique
   *   A unique string that can be provided to make style unique.
   *
   * @return array
   *   The image definition.
   */
  public function getImageDefinition(FileInterface $file, $width = NULL, $height = NULL, $unique = '') {
    $image_uri = $file->getFileUri();
    $image_url = file_create_url($image_uri);
    $definition = [
      'uri' => $image_uri,
      'src' => $image_url,
      'width' => '',
      'height' => '',
      'cache_tags' => [],
    ];
    if ($width || $height) {
      $webp = $this->supportsWebP();
      $image_style = $this->getImageStyle($width, $height, $unique);
      $image_style_uri = $image_style->buildUri($image_uri);
      if (!file_exists($image_style_uri)) {
        $image_style->createDerivative($image_uri, $image_style_uri);
      }
      $info = getimagesize($image_style_uri);
      $width = $info[0];
      $height = $info[1];
      $mime = $info['mime'];
      $definition['uri'] = $image_style_uri;
      $definition['src'] = file_create_url($image_style_uri);
      $definition['webp'] = $webp ? file_create_url($this->getWebp($image_style_uri)) : NULL;
      $definition['width'] = $width;
      $definition['height'] = $height;
      $definition['mime'] = $mime;
      $definition['cache_tags'] = $image_style->getCacheTags();
    }
    return $definition;
  }

  /**
   * Get image preview definition.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file entity that contains the original uri.
   * @param int $width
   *   The image width.
   * @param int $height
   *   The image height.
   * @param string $unique
   *   A unique string that can be provided to make style unique.
   * @param bool $blur
   *   If TRUE, preview will be a blurred image instead of an SVG placeholder.
   *
   * @return array
   *   The image preview definition.
   */
  public function getImagePreviewDefinition(FileInterface $file, $width = NULL, $height = NULL, $unique = '', $blur = FALSE) {
    $image_definition = $this->getImageDefinition($file, $width, $height, $unique);
    $definition = [
      'src' => '',
      'width' => '',
      'height' => '',
      'cache_tags' => [],
    ];
    if ($width || $height) {
      if ($blur) {
        $webp = $this->supportsWebP();
        $specs = $this->getPreviewSpecs($width, $height, $unique);
        $image_uri = $file->getFileUri();
        $image_style = $this->getImageStyle($specs['width'], $specs['height'], $specs['unique'], static::PREVIEW_BLUR_QUALITY);
        $image_style_uri = $image_style->buildUri($image_uri);
        if (!file_exists($image_style_uri)) {
          $image_style->createDerivative($image_uri, $image_style_uri);
        }
        $info = getimagesize($image_style_uri);
        $width = isset($info[0]) ? $info[0] : '';
        $height = isset($info[1]) ? $info[1] : '';
        $mime = $info['mime'];
        $definition['uri'] = $image_style_uri;
        $definition['src'] = file_create_url($image_style_uri);
        $definition['webp'] = $webp ? file_create_url($this->getWebp($image_style_uri)) : NULL;
        $definition['width'] = $image_definition['width'];
        $definition['height'] = $image_definition['height'];
        $definition['mime'] = $mime;
        $definition['cache_tags'] = $image_style->getCacheTags();
      }
      else {
        $definition['src'] = "data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";
        $definition['width'] = $image_definition['width'];
        $definition['height'] = $image_definition['height'];
        $definition['mime'] = 'image/gif';
      }
    }
    return $definition;
  }

  /**
   * Get specs for a preview.
   *
   * @param int $width
   *   The image width.
   * @param int $height
   *   The image height.
   * @param string $unique
   *   A unique string that can be provided to make style unique.
   *
   * @return array
   *   The converted specs.
   */
  public function getPreviewSpecs($width = NULL, $height = NULL, $unique = '') {
    $specs = [
      'width' => 120,
      'height' => 120,
      'unique' => $unique . "{$width}w{$height}h",
      'quality' => static::PREVIEW_BLUR_QUALITY,
    ];
    if ($width && $height) {
      $specs['height'] = round(($height / $width) * $specs['width']);
    }
    elseif ($width) {
      $specs['height'] = NULL;
    }
    else {
      $specs['width'] = NULL;
    }
    return $specs;
  }

  /**
   * Get image style id.
   *
   * @param int $width
   *   The image width.
   * @param int $height
   *   The image height.
   * @param string $unique
   *   A unique string that can be provided to make style unique.
   * @param int $quality
   *   The image quality.
   *
   * @return string
   *   The image style id.
   */
  public function getImageStyleId($width, $height, $unique = '', $quality = NULL) {
    $has_focalpoint = $this->moduleHandler->moduleExists('focal_point');
    $style_id = 'exoimg';
    if ($width) {
      $style_id .= 'w' . $width;
    }
    if ($height) {
      $style_id .= 'h' . $height;
    }
    if ($has_focalpoint && $width && $height) {
      $style_id .= 'f';
    }
    if ($quality) {
      $style_id .= 'q' . $quality;
    }
    if ($unique) {
      $style_id .= $unique;
    }
    return substr($style_id, 0, 32);
  }

  /**
   * Get an image style.
   *
   * @param int $width
   *   The image width.
   * @param int $height
   *   The image height.
   * @param string $unique
   *   A unique string that can be provided to make style unique.
   * @param int $quality
   *   The image quality.
   *
   * @return \Drupal\image\ImageStyleInterface
   *   The image style.
   */
  public function getImageStyle($width, $height, $unique = '', $quality = NULL) {
    $style_storage = $this->entityTypeManager->getStorage('image_style');
    $style_id = $this->getImageStyleId($width, $height, $unique, $quality);
    $style = $style_storage->load($style_id);
    if (!$style) {
      $style = $this->createImageStyle($width, $height, $unique, $quality);
      $style->save();
    }
    return $style;
  }

  /**
   * Create an image style.
   *
   * @param int $width
   *   The image width.
   * @param int $height
   *   The image height.
   * @param string $unique
   *   A unique string that can be provided to make style unique.
   * @param int $quality
   *   The image quality.
   *
   * @return \Drupal\image\ImageStyleInterface
   *   The created image style.
   */
  protected function createImageStyle($width, $height, $unique = '', $quality = NULL) {
    $style_storage = $this->entityTypeManager->getStorage('image_style');
    $has_focalpoint = $this->moduleHandler->moduleExists('focal_point');
    $style_id = $this->getImageStyleId($width, $height, $unique, $quality);
    $style_label = [];
    if ($width) {
      $style_label[] = 'Width: ' . $width;
    }
    if ($height) {
      $style_label[] = 'Height: ' . $height;
    }
    if ($quality) {
      $style_label[] = 'Quality: ' . $quality;
    }
    if ($unique) {
      $style_label[] = 'Unique: ' . $unique;
    }
    $style = $style_storage->create([
      'label' => 'eXo (' . implode(' | ', $style_label) . ')',
      'name' => $style_id,
    ]);
    /** @var \Drupal\image\ImageStyleInterface $style */
    if ($width && $height) {
      $effect_type = 'image_scale_and_crop';
      if ($has_focalpoint) {
        $effect_type = 'focal_point_scale_and_crop';
      }
    }
    else {
      $effect_type = 'image_scale';
    }
    $effect = $this->imageEffectManager->createInstance($effect_type, [
      'uuid' => NULL,
      'id' => $effect_type,
      'weight' => 0,
      'data' => [
        'width' => $width,
        'height' => $height,
      ],
    ]);
    $style->addImageEffect($effect->getConfiguration());
    if ($quality) {
      $configuration = [
        'id' => 'image_style_quality',
        'uuid' => NULL,
        'weight' => 1,
        'data' => [
          'quality' => $quality,
        ],
      ];
      $effect = $this->imageEffectManager->createInstance($configuration['id'], $configuration);
      $style->addImageEffect($effect->getConfiguration());
    }
    return $style;
  }

  /**
   * Delete an image style.
   *
   * @param int $width
   *   The image width.
   * @param int $height
   *   The image height.
   * @param string $unique
   *   A unique string that can be provided to make style unique.
   * @param int $quality
   *   The image quality.
   */
  public function deleteImageStyleByProperties($width, $height, $unique, $quality = NULL) {
    $style_id = $this->getImageStyleId($width, $height, $unique, $quality);
    $this->deleteImageStyleById($style_id);
  }

  /**
   * Delete an image style by style id.
   *
   * @param string $style_id
   *   The image style id.
   */
  public function deleteImageStyleById($style_id) {
    $style_storage = $this->entityTypeManager->getStorage('image_style');
    $style = $style_storage->load($style_id);
    /** @var \Drupal\image\ImageStyleInterface $style */
    if ($style) {
      $style->flush();
      $style->delete();
    }
  }

  /**
   * Get theme breakpoints.
   */
  public function getBreakpoints($theme = NULL) {
    $breakpoints = [];
    if (!$theme) {
      $theme = \Drupal::config('system.theme')->get('default');
    }
    foreach (array_reverse($this->breakpointManager->getBreakpointsByGroup($theme)) as $key => $breakpoint) {
      $parts = explode('.', $key);
      $breakpoints[$parts[1]] = $breakpoint;
    }
    return $breakpoints;
  }

  /**
   * Creates a WebP copy of a source image URI.
   *
   * @param string $uri
   *   Image URI.
   * @param int $quality
   *   Image quality factor.
   *
   * @return bool|string
   *   The location of the WebP image if successful, FALSE if not successful.
   */
  public function getWebp($uri, $quality = NULL) {
    $webp = FALSE;
    $pathInfo = pathinfo($uri);
    $destination = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
    if (!$quality) {
      $quality = $this->exoImagineSettings->getSetting('webp_quality');
    }
    if (file_exists($destination) && filemtime($uri) <= filemtime($destination)) {
      $webp = $destination;
    }
    else {
      // Generate a GD resource from the source image. You can't pass GD
      // resources created by the $imageFactory as a parameter to another
      // function, so we have to do everything in one function.
      $sourceImage = $this->imageFactory->get($uri, 'gd');
      /** @var \Drupal\system\Plugin\ImageToolkit\GDToolkit $toolkit */
      $toolkit = $sourceImage->getToolkit();
      $sourceImage = $toolkit->getResource();

      // If we can generate a GD resource from the source image, generate the
      // URI of the WebP copy and try to create it.
      if ($sourceImage !== NULL) {
        if (function_exists('imagewebp') && @imagewebp($sourceImage, $destination, $quality)) {
          @imagedestroy($sourceImage);
          $webp = $destination;
        }
        elseif (extension_loaded('imagick')) {
          $image = new \Imagick($uri);
          $image->setImageFormat('webp');
          $image->setImageCompressionQuality($quality);
          $image->setImageAlphaChannel(\imagick::ALPHACHANNEL_ACTIVATE);
          $image->setBackgroundColor(new \ImagickPixel('transparent'));
          $image->writeImage(\Drupal::service('file_system')->realpath($destination));
          $webp = $destination;
        }
        else {
          $this->logger->error('Could not generate WebP image.');
        }
      }
    }
    return $webp;
  }

  /**
   * Check if server supports webp conversion.
   *
   * @return bool
   *   TRUE if server supports webp generation.
   */
  public function supportsWebP() {
    if (!$this->exoImagineSettings->getSetting('webp')) {
      return FALSE;
    }
    if (function_exists('imagewebp') || function_exists('imagick')) {
      return in_array('image/webp', \Drupal::request()->getAcceptableContentTypes());
    }
    return FALSE;
  }

}
