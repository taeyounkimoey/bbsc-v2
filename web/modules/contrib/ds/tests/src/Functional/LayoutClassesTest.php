<?php

namespace Drupal\Tests\ds\Functional;

/**
 * Tests for managing layouts and classes on Field UI screen.
 *
 * @group ds
 */
class LayoutClassesTest extends TestBase {

  /**
   * {@inheritdoc}
   */
  protected function setup(): void {
    parent::setup();

    // Set extra fields.
    \Drupal::configFactory()->getEditable('ds_extras.settings')
      ->set('fields_extra', TRUE)
      ->set('fields_extra_list', ['node|article|ds_extras_extra_test_field', 'node|article|ds_extras_second_field'])
      ->save();

    \Drupal::service('entity_field.manager')->clearCachedFieldDefinitions();
  }

  /**
   * Test selecting layouts, classes, region to block and fields.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function testDsTestLayouts() {
    // Check that the ds_3col_equal_width layout is not available (through the
    // alter).
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertSession()->responseNotContains('ds_3col_stacked_equal_width');

    // Create code and block field.
    $this->dsCreateTokenField();
    $this->dsCreateBlockField();

    $layout = [
      'ds_layout' => 'ds_2col_stacked',
    ];

    $assert = [
      'regions' => [
        'header' => '<td colspan="8">' . $this->t('Header') . '</td>',
        'left' => '<td colspan="8">' . $this->t('Left') . '</td>',
        'right' => '<td colspan="8">' . $this->t('Right') . '</td>',
        'footer' => '<td colspan="8">' . $this->t('Footer') . '</td>',
      ],
    ];

    $fields = [
      'fields[node_post_date][region]' => 'header',
      'fields[node_author][region]' => 'left',
      'fields[node_links][region]' => 'left',
      'fields[body][region]' => 'right',
      'fields[dynamic_token_field:node-test_field][region]' => 'left',
      'fields[dynamic_block_field:node-test_block_field][region]' => 'left',
      'fields[node_submitted_by][region]' => 'left',
      'fields[ds_extras_extra_test_field][region]' => 'header',
    ];

    // Setup first layout.
    $this->dsSelectLayout($layout, $assert);
    $this->dsConfigureClasses();
    $this->dsSelectClasses();
    $this->dsConfigureUi($fields);

    // Assert the two extra fields are found.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertSession()->responseContains('ds_extras_extra_test_field');
    $this->assertSession()->responseContains('ds_extras_second_field');

    // Assert we have configuration.
    $entity_manager = \Drupal::entityTypeManager();
    /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $entity_display */
    $entity_display = $entity_manager->getStorage('entity_view_display')->load('node.article.default');
    $data = $entity_display->getThirdPartySettings('ds');

    $this->assertNotEmpty($data, t('Configuration found for layout settings for node article'));
    $this->assertNotEmpty(in_array('ds_extras_extra_test_field', $data['regions']['header']), $this->t('Extra field is in header'));
    $this->assertNotEmpty(in_array('node_post_date', $data['regions']['header']), $this->t('Post date is in header'));
    $this->assertNotEmpty(in_array('dynamic_token_field:node-test_field', $data['regions']['left']), $this->t('Test field is in left'));
    $this->assertNotEmpty(in_array('node_author', $data['regions']['left']), $this->t('Author is in left'));
    $this->assertNotEmpty(in_array('node_links', $data['regions']['left']), $this->t('Links is in left'));
    $this->assertNotEmpty(in_array('dynamic_block_field:node-test_block_field', $data['regions']['left']), $this->t('Test block field is in left'));
    $this->assertNotEmpty(in_array('body', $data['regions']['right']), $this->t('Body is in right'));
    $this->assertNotEmpty(in_array('class_name_1', $data['layout']['settings']['classes']['header']), $this->t('Class name 1 is in header'));
    $this->assertEmpty($data['layout']['settings']['classes']['left'], $this->t('Left has no classes'));
    $this->assertEmpty($data['layout']['settings']['classes']['right'], $this->t('Right has classes'));
    $this->assertNotEmpty(in_array('class_name_2', $data['layout']['settings']['classes']['footer']), $this->t('Class name 2 is in header'));

    // Create a article node and verify settings.
    $settings = [
      'type' => 'article',
    ];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());

    // Assert default classes.
    $this->assertSession()->responseContains('node node--type-article node--view-mode-full');

    // Assert regions.
    $this->assertSession()->responseContains('group-header');
    $this->assertSession()->responseContains('class_name_1 group-header');
    $this->assertSession()->responseContains('group-left');
    $this->assertSession()->responseContains('group-right');
    $this->assertSession()->responseContains('group-footer');
    $this->assertSession()->responseContains('class_name_2 group-footer');

    // Assert custom fields.
    $this->assertSession()->responseContains('field--name-dynamic-token-fieldnode-test-field');
    $this->assertSession()->responseContains('field--name-dynamic-block-fieldnode-test-block-field');

    $this->assertSession()->responseContains('Submitted by');
    $this->assertSession()->pageTextContains('This is an extra field made available through "Extra fields" functionality.');

    // Test HTML5 wrappers.
    $this->assertSession()->responseNotContains('<header class="class_name_1 group-header');
    $this->assertSession()->responseNotContains('<footer class="group-right');
    $this->assertSession()->responseNotContains('<article');
    $wrappers = [
      'layout_configuration[layout_options][header]' => 'header',
      'layout_configuration[layout_options][right]' => 'footer',
      'layout_configuration[layout_options][outer_wrapper]' => 'article',
      'layout_configuration[layout_options][attributes]' => 'class|test-class,role|testing-role',
    ];
    $this->dsConfigureUi($wrappers);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->responseContains('<header class="class_name_1 group-header');
    $this->assertSession()->responseContains('<footer class="group-right');
    $this->assertSession()->responseContains('<article');
    $this->assertSession()->responseContains('test-class');
    $this->assertSession()->responseContains('testing-role');

    // Remove all the node classes.
    $edit = ['layout_configuration[layout_options][entity_classes]' => 'no_classes'];
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->submitForm($edit, t('Save'));
    $this->drupalGet('node/' . $node->id());

    // Assert that there are no entity classes.
    $this->assertSession()->responseNotContains('node node--type-article node--view-mode-full');

    // Test that a default view mode with no layout is not affected by a
    // disabled view mode.
    $edit = [
      'ds_layout' => '_none',
      'display_modes_custom[full]' => FALSE,
    ];
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->submitForm($edit, 'Save');

    $elements = $this->xpath('//*[@id="edit-fields-body-region"]');

    $this->assertNotEmpty($elements[0]->find('xpath', '//option[@value = "content" and @selected = "selected"]'));
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->pageTextNotContains('Test code field on node 1');
  }

}
