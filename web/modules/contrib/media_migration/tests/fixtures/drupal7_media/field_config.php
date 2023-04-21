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

$connection->schema()->createTable('field_config', array(
  'fields' => array(
    'id' => array(
      'type' => 'serial',
      'not null' => TRUE,
      'size' => 'normal',
    ),
    'field_name' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '32',
    ),
    'type' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '128',
    ),
    'module' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '128',
      'default' => '',
    ),
    'active' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'tiny',
      'default' => '0',
    ),
    'storage_type' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '128',
    ),
    'storage_module' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '128',
      'default' => '',
    ),
    'storage_active' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'tiny',
      'default' => '0',
    ),
    'locked' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'tiny',
      'default' => '0',
    ),
    'data' => array(
      'type' => 'blob',
      'not null' => TRUE,
      'size' => 'big',
    ),
    'cardinality' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'tiny',
      'default' => '0',
    ),
    'translatable' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'tiny',
      'default' => '0',
    ),
    'deleted' => array(
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'tiny',
      'default' => '0',
    ),
  ),
  'primary key' => array(
    'id',
  ),
  'indexes' => array(
    'field_name' => array(
      'field_name',
    ),
    'active' => array(
      'active',
    ),
    'storage_active' => array(
      'storage_active',
    ),
    'deleted' => array(
      'deleted',
    ),
    'module' => array(
      'module',
    ),
    'storage_module' => array(
      'storage_module',
    ),
    'type' => array(
      'type',
    ),
    'storage_type' => array(
      'storage_type',
    ),
  ),
  'mysql_character_set' => 'utf8',
));

$connection->insert('field_config')
->fields(array(
  'id',
  'field_name',
  'type',
  'module',
  'active',
  'storage_type',
  'storage_module',
  'storage_active',
  'locked',
  'data',
  'cardinality',
  'translatable',
  'deleted',
))
->values(array(
  'id' => '1',
  'field_name' => 'comment_body',
  'type' => 'text_long',
  'module' => 'text',
  'active' => '1',
  'storage_type' => 'field_sql_storage',
  'storage_module' => 'field_sql_storage',
  'storage_active' => '1',
  'locked' => '0',
  'data' => 'a:6:{s:12:"entity_types";a:1:{i:0;s:7:"comment";}s:12:"translatable";b:0;s:8:"settings";a:0:{}s:7:"storage";a:4:{s:4:"type";s:17:"field_sql_storage";s:8:"settings";a:0:{}s:6:"module";s:17:"field_sql_storage";s:6:"active";i:1;}s:12:"foreign keys";a:1:{s:6:"format";a:2:{s:5:"table";s:13:"filter_format";s:7:"columns";a:1:{s:6:"format";s:6:"format";}}}s:7:"indexes";a:1:{s:6:"format";a:1:{i:0;s:6:"format";}}}',
  'cardinality' => '1',
  'translatable' => '0',
  'deleted' => '0',
))
->values(array(
  'id' => '2',
  'field_name' => 'body',
  'type' => 'text_with_summary',
  'module' => 'text',
  'active' => '1',
  'storage_type' => 'field_sql_storage',
  'storage_module' => 'field_sql_storage',
  'storage_active' => '1',
  'locked' => '0',
  'data' => 'a:6:{s:12:"entity_types";a:1:{i:0;s:4:"node";}s:12:"translatable";b:0;s:8:"settings";a:0:{}s:7:"storage";a:4:{s:4:"type";s:17:"field_sql_storage";s:8:"settings";a:0:{}s:6:"module";s:17:"field_sql_storage";s:6:"active";i:1;}s:12:"foreign keys";a:1:{s:6:"format";a:2:{s:5:"table";s:13:"filter_format";s:7:"columns";a:1:{s:6:"format";s:6:"format";}}}s:7:"indexes";a:1:{s:6:"format";a:1:{i:0;s:6:"format";}}}',
  'cardinality' => '1',
  'translatable' => '0',
  'deleted' => '0',
))
->values(array(
  'id' => '4',
  'field_name' => 'field_image',
  'type' => 'image',
  'module' => 'image',
  'active' => '1',
  'storage_type' => 'field_sql_storage',
  'storage_module' => 'field_sql_storage',
  'storage_active' => '1',
  'locked' => '0',
  'data' => 'a:7:{s:7:"indexes";a:1:{s:3:"fid";a:1:{i:0;s:3:"fid";}}s:8:"settings";a:2:{s:10:"uri_scheme";s:6:"public";s:13:"default_image";i:0;}s:7:"storage";a:5:{s:4:"type";s:17:"field_sql_storage";s:8:"settings";a:0:{}s:6:"module";s:17:"field_sql_storage";s:6:"active";s:1:"1";s:7:"details";a:1:{s:3:"sql";a:2:{s:18:"FIELD_LOAD_CURRENT";a:1:{s:22:"field_data_field_image";a:5:{s:3:"fid";s:15:"field_image_fid";s:3:"alt";s:15:"field_image_alt";s:5:"title";s:17:"field_image_title";s:5:"width";s:17:"field_image_width";s:6:"height";s:18:"field_image_height";}}s:19:"FIELD_LOAD_REVISION";a:1:{s:26:"field_revision_field_image";a:5:{s:3:"fid";s:15:"field_image_fid";s:3:"alt";s:15:"field_image_alt";s:5:"title";s:17:"field_image_title";s:5:"width";s:17:"field_image_width";s:6:"height";s:18:"field_image_height";}}}}}s:12:"entity_types";a:0:{}s:12:"translatable";s:1:"0";s:12:"foreign keys";a:1:{s:3:"fid";a:2:{s:5:"table";s:12:"file_managed";s:7:"columns";a:1:{s:3:"fid";s:3:"fid";}}}s:2:"id";s:1:"4";}',
  'cardinality' => '1',
  'translatable' => '0',
  'deleted' => '0',
))
->values(array(
  'id' => '5',
  'field_name' => 'field_file_image_alt_text',
  'type' => 'text',
  'module' => 'text',
  'active' => '1',
  'storage_type' => 'field_sql_storage',
  'storage_module' => 'field_sql_storage',
  'storage_active' => '1',
  'locked' => '0',
  'data' => 'a:6:{s:12:"entity_types";a:0:{}s:12:"foreign keys";a:1:{s:6:"format";a:2:{s:5:"table";s:13:"filter_format";s:7:"columns";a:1:{s:6:"format";s:6:"format";}}}s:7:"indexes";a:1:{s:6:"format";a:1:{i:0;s:6:"format";}}s:8:"settings";a:1:{s:10:"max_length";s:3:"255";}s:12:"translatable";s:1:"0";s:7:"storage";a:4:{s:4:"type";s:17:"field_sql_storage";s:8:"settings";a:0:{}s:6:"module";s:17:"field_sql_storage";s:6:"active";i:1;}}',
  'cardinality' => '1',
  'translatable' => '0',
  'deleted' => '0',
))
->values(array(
  'id' => '6',
  'field_name' => 'field_file_image_title_text',
  'type' => 'text',
  'module' => 'text',
  'active' => '1',
  'storage_type' => 'field_sql_storage',
  'storage_module' => 'field_sql_storage',
  'storage_active' => '1',
  'locked' => '0',
  'data' => 'a:6:{s:12:"entity_types";a:0:{}s:12:"foreign keys";a:1:{s:6:"format";a:2:{s:5:"table";s:13:"filter_format";s:7:"columns";a:1:{s:6:"format";s:6:"format";}}}s:7:"indexes";a:1:{s:6:"format";a:1:{i:0;s:6:"format";}}s:8:"settings";a:1:{s:10:"max_length";s:3:"255";}s:12:"translatable";s:1:"0";s:7:"storage";a:4:{s:4:"type";s:17:"field_sql_storage";s:8:"settings";a:0:{}s:6:"module";s:17:"field_sql_storage";s:6:"active";i:1;}}',
  'cardinality' => '1',
  'translatable' => '0',
  'deleted' => '0',
))
->values(array(
  'id' => '7',
  'field_name' => 'field_media',
  'type' => 'file',
  'module' => 'file',
  'active' => '1',
  'storage_type' => 'field_sql_storage',
  'storage_module' => 'field_sql_storage',
  'storage_active' => '1',
  'locked' => '0',
  'data' => 'a:7:{s:12:"translatable";s:1:"0";s:12:"entity_types";a:0:{}s:8:"settings";a:3:{s:13:"display_field";i:0;s:15:"display_default";i:0;s:10:"uri_scheme";s:6:"public";}s:7:"storage";a:5:{s:4:"type";s:17:"field_sql_storage";s:8:"settings";a:0:{}s:6:"module";s:17:"field_sql_storage";s:6:"active";s:1:"1";s:7:"details";a:1:{s:3:"sql";a:2:{s:18:"FIELD_LOAD_CURRENT";a:1:{s:22:"field_data_field_media";a:3:{s:3:"fid";s:15:"field_media_fid";s:7:"display";s:19:"field_media_display";s:11:"description";s:23:"field_media_description";}}s:19:"FIELD_LOAD_REVISION";a:1:{s:26:"field_revision_field_media";a:3:{s:3:"fid";s:15:"field_media_fid";s:7:"display";s:19:"field_media_display";s:11:"description";s:23:"field_media_description";}}}}}s:12:"foreign keys";a:1:{s:3:"fid";a:2:{s:5:"table";s:12:"file_managed";s:7:"columns";a:1:{s:3:"fid";s:3:"fid";}}}s:7:"indexes";a:1:{s:3:"fid";a:1:{i:0;s:3:"fid";}}s:2:"id";s:1:"7";}',
  'cardinality' => '-1',
  'translatable' => '0',
  'deleted' => '0',
))
->values(array(
  'id' => '8',
  'field_name' => 'field_media_integer',
  'type' => 'number_integer',
  'module' => 'number',
  'active' => '1',
  'storage_type' => 'field_sql_storage',
  'storage_module' => 'field_sql_storage',
  'storage_active' => '1',
  'locked' => '0',
  'data' => 'a:7:{s:12:"translatable";s:1:"0";s:12:"entity_types";a:0:{}s:8:"settings";a:0:{}s:7:"storage";a:5:{s:4:"type";s:17:"field_sql_storage";s:8:"settings";a:0:{}s:6:"module";s:17:"field_sql_storage";s:6:"active";s:1:"1";s:7:"details";a:1:{s:3:"sql";a:2:{s:18:"FIELD_LOAD_CURRENT";a:1:{s:30:"field_data_field_media_integer";a:1:{s:5:"value";s:25:"field_media_integer_value";}}s:19:"FIELD_LOAD_REVISION";a:1:{s:34:"field_revision_field_media_integer";a:1:{s:5:"value";s:25:"field_media_integer_value";}}}}}s:12:"foreign keys";a:0:{}s:7:"indexes";a:0:{}s:2:"id";s:1:"8";}',
  'cardinality' => '1',
  'translatable' => '0',
  'deleted' => '0',
))
->execute();
