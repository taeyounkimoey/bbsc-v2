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

$connection->schema()->createTable('sequences', array(
  'fields' => array(
    'value' => array(
      'type' => 'serial',
      'not null' => TRUE,
      'size' => 'normal',
      'unsigned' => TRUE,
    ),
  ),
  'primary key' => array(
    'value',
  ),
  'mysql_character_set' => 'utf8',
));

$connection->insert('sequences')
->fields(array(
  'value',
))
->values(array(
  'value' => '2',
))
->execute();
