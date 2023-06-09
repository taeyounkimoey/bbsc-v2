<?php
// phpcs:ignoreFile
/**
 * @file
 * A database agnostic dump for testing purposes.
 *
 * This file was generated by the Drupal 9.2.6 db-tools.php script.
 */

use Drupal\Core\Database\Database;

$connection = Database::getConnection();

$connection->insert('node')
->fields(array(
  'nid',
  'vid',
  'type',
  'language',
  'title',
  'uid',
  'status',
  'created',
  'changed',
  'comment',
  'promote',
  'sticky',
  'tnid',
  'translate',
))
->values(array(
  'nid' => '3273427',
  'vid' => '3273427',
  'type' => 'vef_content',
  'language' => 'und',
  'title' => 'vid embed example article ',
  'uid' => '1',
  'status' => '1',
  'created' => '1648796489',
  'changed' => '1649053729',
  'comment' => '2',
  'promote' => '1',
  'sticky' => '0',
  'tnid' => '0',
  'translate' => '0',
))
->values(array(
  'nid' => '3273428',
  'vid' => '3273428',
  'type' => 'vef_content',
  'language' => 'und',
  'title' => 'vimeo example ',
  'uid' => '1',
  'status' => '1',
  'created' => '1648799066',
  'changed' => '1648808166',
  'comment' => '2',
  'promote' => '1',
  'sticky' => '0',
  'tnid' => '0',
  'translate' => '0',
))
->execute();
