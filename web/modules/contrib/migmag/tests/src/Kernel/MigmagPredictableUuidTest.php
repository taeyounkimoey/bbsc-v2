<?php

namespace Drupal\Tests\migmag\Kernel;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Entity\ContentEntityStorageBase;
use Drupal\KernelTests\KernelTestBase;
use Drupal\migmag_predictable_uuid\PredictableUuid;
use Drupal\node\Entity\Node;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Tests predictable UUID generator.
 *
 * @group migmag
 */
class MigmagPredictableUuidTest extends KernelTestBase {

  use ContentTypeCreationTrait;
  use UserCreationTrait;

  /**
   * A regular expression for UUIDs.
   *
   * @const string
   */
  const UUID_REGEXP = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}$/';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'migmag_predictable_uuid',
    'system',
  ];

  /**
   * Tests predictable UUID generator.
   *
   * @param array[] $watched_classes
   *   The list of watched classes, grouped by the UUID prefix or UUID template
   *   string.
   * @param bool|null $with_site_install
   *   Whether the UUID generator should be tested with 'system.install'.
   * @param string|null $expected_uuid
   *   The expected generated UUID. Can be null only if we don't expect a
   *   predixtable UUID.
   * @param int $initial_count
   *   The initial count to set for each prefix / UUID template.
   * @param int[] $expected_counts
   *   The array of the expected counts after we tried to generate a UUID. We
   *   expect that the matching prefix' count was increased by 1, or it is set
   *   to 0 if we reached PHP_INT_MAX.
   *
   * @dataProvider providerTestUuidGenerator
   */
  public function testUuidGenerator(array $watched_classes, ?bool $with_site_install = FALSE, ?string $expected_uuid = NULL, int $initial_count = 0, array $expected_counts = []): void {
    $this->container->get('state')->set(PredictableUuid::WATCHED_CLASSES_STATE_KEY, $watched_classes);
    foreach (array_keys($watched_classes) as $prefix) {
      $this->container->get('state')->set(PredictableUuid::LAST_SUFFIX_STATE_KEY . '.' . $prefix, $initial_count);
    }

    if ($with_site_install) {
      $this->assertNull(
        \Drupal::configFactory()->getEditable('system.site')->get('uuid')
      );
      \Drupal::moduleHandler()->loadInclude('system', 'install');
      system_install();
      $generated_uuid = \Drupal::configFactory()->getEditable('system.site')->get('uuid');
    }
    else {
      $generator = $this->container->get('uuid');
      $this->assertInstanceOf(UuidInterface::class, $generator);
      $generated_uuid = $generator->generate();
    }

    if ($expected_uuid) {
      $this->assertSame($expected_uuid, $generated_uuid);
    }
    else {
      // Validate the UUID.
      $this->assertMatchesRegularExpression(self::UUID_REGEXP, $generated_uuid);
    }

    foreach ($expected_counts as $prefix => $expected_count) {
      $this->assertSame(
        $expected_count,
        $this->container->get('state')->get(PredictableUuid::LAST_SUFFIX_STATE_KEY . '.' . $prefix),
        $prefix
      );
    }
  }

  /**
   * Test data provider for ::testUuidGenerator.
   *
   * @return array[]
   *   The test cases,
   */
  public function providerTestUuidGenerator(): array {
    $site_install_path = implode(
      DIRECTORY_SEPARATOR,
      [
        'core',
        'modules',
        'system',
        'system.install',
      ]
    );
    return [
      'No watching' => [
        'watched' => [],
      ],

      'With non-matching watching' => [
        'watched' => ['foo' => [$site_install_path]],
        'with site install' => FALSE,
        'expected uuid' => NULL,
        'initial count' => 0,
        'expected counts' => [
          'foo' => 0,
        ],
      ],

      'With matching watching' => [
        'watched' => [
          'aaaaaaaa-bbbb-4ccc-dddd-000000000000' => [get_class($this)],
        ],
        'with site install' => FALSE,
        'expected uuid' => 'aaaaaaaa-bbbb-4ccc-dddd-000000000001',
        'initial count' => 0,
        'expected counts' => [
          'aaaaaaaa-bbbb-4ccc-dddd-000000000000' => 1,
        ],
      ],

      'Order #1' => [
        'watched' => [
          'first' => [$site_install_path],
          'second' => [get_class($this)],
        ],
        'with site install' => TRUE,
        'expected uuid' => 'first1',
        'initial count' => 0,
        'expected counts' => [
          'first' => 1,
          'second' => 0,
        ],
      ],

      'Order #2' => [
        'watched' => [
          'first' => [get_class($this)],
          'second' => [$site_install_path],
        ],
        'with site install' => TRUE,
        'expected uuid' => 'first1',
        'initial count' => 0,
        'expected counts' => [
          'first' => 1,
          'second' => 0,
        ],
      ],

      'Initial count is respected' => [
        'watched' => [
          'foo' => [get_class($this)],
        ],
        'with site install' => FALSE,
        'expected uuid' => 'foo127',
        'initial count' => 126,
        'expected counts' => [
          'foo' => 127,
        ],
      ],

      'Int max' => [
        'watched' => [
          'bar' => [get_class($this)],
        ],
        'with site install' => TRUE,
        'expected uuid' => 'bar0',
        'initial count' => PHP_INT_MAX,
        'expected counts' => [
          'bar' => 0,
        ],
      ],

      'Int max - 1' => [
        'watched' => [
          'baz' => [get_class($this)],
        ],
        'with site install' => TRUE,
        'expected uuid' => 'baz' . PHP_INT_MAX,
        'initial count' => PHP_INT_MAX - 1,
        'expected counts' => [
          'baz' => PHP_INT_MAX,
        ],
      ],
    ];
  }

  /**
   * Tests predictable UUID generator with nodes.
   */
  public function testUuidGeneratorWithNode(): void {
    $this->enableModules(['field', 'filter', 'text', 'user', 'node']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig('node');

    // We have to create at least an anonymous user.
    // @see https://drupal.org/i/3056234#comment-13275077
    $anonymous = $this->createUser([], '', FALSE, [
      'uid' => 0,
      'langcode' => 'und',
    ]);
    $this->setCurrentUser($anonymous);

    $this->container->get('state')->set(
      PredictableUuid::WATCHED_CLASSES_STATE_KEY,
      ['content-uuid-' => [ContentEntityStorageBase::class]]
    );

    $type = $this->createContentType();
    $node1 = Node::create(['title' => 'Node 1', 'type' => $type->id()]);
    $node2 = Node::create(['title' => 'Node 2', 'type' => $type->id()]);
    $node3 = Node::create(['title' => 'Node 3', 'type' => $type->id()]);

    $this->assertSame('content-uuid-1', $node1->uuid());
    $this->assertSame('content-uuid-2', $node2->uuid());
    $this->assertSame('content-uuid-3', $node3->uuid());
  }

}
