<?php

namespace Drupal\Tests\migmag_rollbackable\Kernel;

use Drupal\migrate\MigrateExecutable;

/**
 * Tests the 'migmag_rollbackable_theme_settings' destination.
 *
 * @coversDefaultClass \Drupal\migmag_rollbackable\Plugin\migrate\destination\RollbackableThemeSettings
 *
 * @group migmag_rollbackable
 */
class RollbackableThemeSettingsTest extends RollbackableDestinationTestBase {

  /**
   * The ID of the tested target configuration.
   *
   * @const string
   */
  const TARGET_CONFIG_ID = 'bartik.settings';

  /**
   * Base definition for the test migrations.
   *
   * @const array
   */
  const THEME_SETTINGS_MIGRATION_BASE = [
    'source' => [
      'plugin' => 'embedded_data',
      'data_rows' => [
        [
          'name' => 'theme_bartik_settings',
          'configuration_name' => 'bartik.settings',
          'toggle_logo' => FALSE,
          'toggle_name' => FALSE,
          'toggle_slogan' => TRUE,
        ],
      ],
      'ids' => [
        'configuration_name' => ['type' => 'string'],
      ],
    ],
    'process' => [
      'configuration_name' => 'configuration_name',
      'toggle_logo' => 'toggle_logo',
      'toggle_name' => 'toggle_name',
      'toggle_slogan' => 'toggle_slogan',
    ],
    'destination' => [
      'plugin' => 'migmag_rollbackable_theme_settings',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->container->get('theme_installer')->install(['bartik']);
  }

  /**
   * Tests the rollbackability of 'rollbackable_d7_theme_settings' destination.
   *
   * @dataProvider providerTestMigrationRollback
   */
  public function testD7ThemeSettingsRollback(bool $with_preexisting_config = FALSE) {
    $bartik_settings = $this->config(self::TARGET_CONFIG_ID);
    $this->assertTrue($bartik_settings->isNew());

    if ($with_preexisting_config) {
      $bartik_settings
        ->set('features', ['logo' => TRUE])
        ->save();
      $this->assertFalse($this->config(self::TARGET_CONFIG_ID)->isNew());
    }

    $bartik_settings_before_migration = $this->config(self::TARGET_CONFIG_ID)->getRawData();

    // Import...
    $base_executable = new MigrateExecutable($this->baseMigration(), $this);
    $this->startCollectingMessages();
    $base_executable->import();
    $this->assertNoErrors();

    $expected_bartik_settings_after_base_migration = $bartik_settings_before_migration;
    $expected_bartik_settings_after_base_migration['features']['logo'] = FALSE;
    $expected_bartik_settings_after_base_migration['features']['name'] = FALSE;
    $expected_bartik_settings_after_base_migration['features']['slogan'] = TRUE;
    $this->assertEquals(
      $expected_bartik_settings_after_base_migration,
      $this->config(self::TARGET_CONFIG_ID)->getRawData()
    );

    $subsequent_executable = new MigrateExecutable($this->subsequentMigration(), $this);
    $this->startCollectingMessages();
    $subsequent_executable->import();
    $this->assertNoErrors();

    $expected_bartik_settings_after_subsequent_migration = $expected_bartik_settings_after_base_migration;
    $expected_bartik_settings_after_subsequent_migration['features']['name'] = TRUE;

    $this->assertEquals(
      $expected_bartik_settings_after_subsequent_migration,
      $this->config(self::TARGET_CONFIG_ID)->getRawData()
    );

    $subsequent_executable->rollback();

    $this->assertEquals(
      $expected_bartik_settings_after_base_migration,
      $this->config(self::TARGET_CONFIG_ID)->getRawData()
    );

    // Roll back the base migration.
    $base_executable->rollback();

    $this->assertEquals(
      $bartik_settings_before_migration,
      $this->config(self::TARGET_CONFIG_ID)->getRawData()
    );

    if ($with_preexisting_config) {
      $this->assertFalse($this->config(self::TARGET_CONFIG_ID)->isNew());
    }
    else {
      $this->assertTrue($this->config(self::TARGET_CONFIG_ID)->isNew());
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function baseMigration() {
    $definition = ['id' => 'theme_settings_base'] + self::THEME_SETTINGS_MIGRATION_BASE;
    return $this->getMigrationPluginInstance($definition);
  }

  /**
   * {@inheritdoc}
   */
  protected function subsequentMigration() {
    $definition = self::THEME_SETTINGS_MIGRATION_BASE;
    $definition['id'] = 'theme_settings_subsequent';
    $definition['source']['data_rows'] = [
      [
        'name' => 'theme_bartik_settings',
        'configuration_name' => 'bartik.settings',
        'toggle_logo' => FALSE,
        'toggle_name' => TRUE,
        'toggle_slogan' => TRUE,
      ],
    ];

    return $this->getMigrationPluginInstance($definition);
  }

  /**
   * {@inheritdoc}
   */
  protected function baseTranslationMigration() {
    // Theme settings cannot have translation destination.
  }

  /**
   * {@inheritdoc}
   */
  protected function subsequentTranslationMigration() {
    // Theme settings cannot have translation destination.
  }

}
