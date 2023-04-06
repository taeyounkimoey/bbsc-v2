<?php

namespace Drupal\ds\Plugin\DsField\Book;

use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Plugin that the book navigation.
 *
 * @DsField(
 *   id = "book_navigation",
 *   title = @Translation("Book navigation"),
 *   entity_type = "node",
 *   provider = "book"
 * )
 */
class BookNavigation extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function isAllowed() {

    // We only allow the 'full' and 'default' view mode.
    if (!in_array($this->viewMode(), ['default', 'full'])) {
      return FALSE;
    }

    return TRUE;
  }

}
