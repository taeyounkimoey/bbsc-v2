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

$connection->schema()->createTable('file_usage', array(
  'fields' => array(
    'fid' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'normal',
      'unsigned' => TRUE,
    ),
    'module' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '255',
      'default' => '',
    ),
    'type' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '64',
      'default' => '',
    ),
    'id' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'normal',
      'default' => '0',
      'unsigned' => TRUE,
    ),
    'count' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'normal',
      'default' => '0',
      'unsigned' => TRUE,
    ),
  ),
  'primary key' => array(
    'fid',
    'type',
    'id',
    'module',
  ),
  'indexes' => array(
    'type_id' => array(
      'type',
      'id',
    ),
    'fid_count' => array(
      'fid',
      'count',
    ),
    'fid_module' => array(
      'fid',
      array(
        'module',
        '191',
      ),
    ),
  ),
  'mysql_character_set' => 'utf8',
));

$connection->insert('file_usage')
->fields(array(
  'fid',
  'module',
  'type',
  'id',
  'count',
))
->values(array(
  'fid' => '1',
  'module' => 'media',
  'type' => 'node',
  'id' => '1',
  'count' => '1',
))
->values(array(
  'fid' => '2',
  'module' => 'file',
  'type' => 'node',
  'id' => '1',
  'count' => '1',
))
->values(array(
  'fid' => '3',
  'module' => 'file',
  'type' => 'node',
  'id' => '1',
  'count' => '1',
))
->values(array(
  'fid' => '4',
  'module' => 'file',
  'type' => 'node',
  'id' => '1',
  'count' => '1',
))
->values(array(
  'fid' => '7',
  'module' => 'file',
  'type' => 'node',
  'id' => '2',
  'count' => '1',
))
->execute();
