<?php

namespace Drupal\media;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for media.
 *
 * The default storage is overridden to handle metadata fetching outside of the
 * database transaction.
 */
class MediaStorage extends SqlContentEntityStorage {

  /**
   * {@inheritdoc}
   */
  public function save(EntityInterface $media) {
    /** @var \Drupal\ls_migration\MigrationStatus $migration_status */
    $migration_status = \Drupal::service('ls_migration.migration_status');
    if ($migration_status->isImporting()) {
      return;
    }

    // For backwards compatibility, modules that override the Media entity
    // class, are not required to implement the prepareSave() method.
    // @todo For Drupal 8.7, consider throwing a deprecation notice if the
    //   method doesn't exist. See
    //   https://www.drupal.org/project/drupal/issues/2992426 for further
    //   discussion.
    if (method_exists($media, 'prepareSave')) {
      $media->prepareSave();
    }
    return parent::save($media);
  }

}
