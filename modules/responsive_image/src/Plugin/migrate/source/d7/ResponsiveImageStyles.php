<?php

namespace Drupal\responsive_image\Plugin\migrate\source\d7;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Drupal responsive image styles source from database.
 *
 * Breakpoints are YAML files in Drupal 8. If you have a custom
 * theme and want to migrate its responsive image styles to
 * Drupal 8, create the respective yourtheme.breakpoints.yml file at
 * the root of the theme.
 *
 * @see https://www.drupal.org/docs/8/theming-drupal-8/working-with-breakpoints-in-drupal-8
 *
 * @MigrateSource(
 *   id = "d7_responsive_image_styles",
 *   source_module = "picture"
 * )
 */
class ResponsiveImageStyles extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('picture_mapping', 'p')
      ->fields('p', ['label', 'machine_name', 'breakpoint_group', 'mapping']);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'label' => $this->t('The machine name of the mapping'),
      'machine_name' => $this->t('The machine name of the mapping'),
      'breakpoint_group' => $this->t('The group this mapping belongs to'),
      'mapping' => $this->t('The mappings linked to the breakpoints group'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['machine_name']['type'] = 'string';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $mapping = unserialize($row->getSourceProperty('mapping'));
    // Image style names may have dashes. These need to be underscores.
    // The structure of mapping is like the following one:
    // <code>
    // [breakpoints.theme.lifestyle.computer] => Array(
    //   [1x] => Array(
    //     [mapping_type] => image_style
    //     [image_style] => blog-post-embedded--computer
    //   )
    //   [1.5x] => Array(
    //     [mapping_type] => image_style
    //     [image_style] => blog-post-embedded--computer-1_5x
    //   )
    // )
    // [breakpoints.theme.lifestyle.tablet] => Array(
    // </code>
    // We need to loop the above and fix image style names or they won't match.
    foreach ($mapping as $breakpoint_id => $multipliers) {
      foreach ($multipliers as $multiplier_id => $multiplier) {
        if (isset($multiplier['image_style'])) {
          $image_style = $mapping[$breakpoint_id][$multiplier_id]['image_style'];
          $mapping[$breakpoint_id][$multiplier_id]['image_style'] = str_replace('-', '_', $image_style);
        }
      }
    }
    $row->setSourceProperty('mapping', $mapping);
    return parent::prepareRow($row);
  }

}
