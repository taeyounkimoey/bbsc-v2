<?php
// phpcs:ignoreFile
/**
 * @file
 * A database agnostic dump for testing purposes.
 *
 * This file was generated by the Drupal 9.2.10 db-tools.php script.
 */

use Drupal\Core\Database\Database;

$connection = Database::getConnection();

$connection->schema()->createTable('date_format_locale', array(
  'fields' => array(
    'format' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '100',
    ),
    'type' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '64',
    ),
    'language' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '12',
    ),
  ),
  'primary key' => array(
    'type',
    'language',
  ),
  'mysql_character_set' => 'utf8',
));
