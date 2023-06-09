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

$connection->schema()->createTable('node_revision', array(
  'fields' => array(
    'nid' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'normal',
      'default' => '0',
      'unsigned' => TRUE,
    ),
    'vid' => array(
      'type' => 'serial',
      'not null' => TRUE,
      'size' => 'normal',
      'unsigned' => TRUE,
    ),
    'uid' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'normal',
      'default' => '0',
    ),
    'title' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '255',
      'default' => '',
    ),
    'log' => array(
      'type' => 'text',
      'not null' => TRUE,
      'size' => 'big',
    ),
    'timestamp' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'normal',
      'default' => '0',
    ),
    'status' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'normal',
      'default' => '1',
    ),
    'comment' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'normal',
      'default' => '0',
    ),
    'promote' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'normal',
      'default' => '0',
    ),
    'sticky' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'normal',
      'default' => '0',
    ),
  ),
  'primary key' => array(
    'vid',
  ),
  'indexes' => array(
    'nid' => array(
      'nid',
    ),
    'uid' => array(
      'uid',
    ),
  ),
  'mysql_character_set' => 'utf8',
));

$connection->insert('node_revision')
->fields(array(
  'nid',
  'vid',
  'uid',
  'title',
  'log',
  'timestamp',
  'status',
  'comment',
  'promote',
  'sticky',
))
->values(array(
  'nid' => '1',
  'vid' => '1',
  'uid' => '2',
  'title' => 'Article with images and files',
  'log' => '',
  'timestamp' => '1594368881',
  'status' => '1',
  'comment' => '2',
  'promote' => '1',
  'sticky' => '0',
))
->values(array(
  'nid' => '2',
  'vid' => '2',
  'uid' => '1',
  'title' => 'Another article with audio and video files',
  'log' => '',
  'timestamp' => '1597409263',
  'status' => '1',
  'comment' => '0',
  'promote' => '1',
  'sticky' => '0',
))
->execute();
