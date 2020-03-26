<?php

namespace Drupal\exo_config_file\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\exo_link\Plugin\Block\LinkBlock;
use Drupal\Core\Url;

/**
 * Provides a block to display a config file svg.
 *
 * @Block(
 *   id = "svg",
 *   admin_label = @Translation("SVG"),
 * )
 */
class SvgBlock extends LinkBlock {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'svg' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $svg = $this->configuration['svg'];

    $form['title']['#title'] = $this->t('Alt text');
    $form['uri']['#required'] = FALSE;

    $form['svg'] = [
      '#type' => 'exo_config_file',
      '#title' => !$svg ? $this->t('SVG') : $this->t('Replace SVG'),
      '#default_value' => $svg,
      '#extensions' => ['svg'],
      '#required' => empty($svg),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['svg'] = $form_state->getValue('svg');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    // Render as SVG tag.
    $svgRaw = $this->fileGetContents();
    if ($svgRaw) {
      $svgRaw = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $svgRaw);
      $svgRaw = trim($svgRaw);
      if (!empty($this->configuration['title'])) {
        $svgRaw = preg_replace('#<title.*?>.*<\/title>#s', "", $svgRaw);
        $svgRaw = str_replace('</svg>', '<title>' . $this->configuration['title'] . '</title></svg>', $svgRaw);
      }
      if (!empty($this->configuration['uri'])) {
        $build['svg'] = [
          '#title' => Markup::create($svgRaw),
          '#type' => 'link',
          '#url' => Url::fromUri($this->configuration['uri']),
        ];
      }
      else {
        $build['svg'] = [
          '#markup' => Markup::create($svgRaw),
        ];
      }
    }
    return $build;
  }

  /**
   * Provides content of the file.
   *
   * @return string
   *   File content.
   */
  protected function fileGetContents() {
    if (file_exists($this->configuration['svg'])) {
      return file_get_contents($this->configuration['svg']);
    }
    return FALSE;
  }

}
