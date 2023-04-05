<?php

namespace Drupal\ds;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\Display\EntityDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Site\Settings;

class EntityViewAlter implements EntityViewAlterInterface {

  /**
   * {@inheritdoc}
   */
  public function entityViewAlter(&$build, EntityInterface $entity, EntityDisplayInterface $display) {

    static $field_permissions = FALSE;
    static $loaded = FALSE;

    $entity_type = $entity->getEntityTypeId();
    $bundle = $entity->bundle();
    $view_mode = $display->getMode();
    if ($view_mode == 'default') {
      $view_mode = 'full';
    }

    // Add extra metadata needed for contextual links.
    if (isset($build['#contextual_links'][$entity_type])) {
      $build['#contextual_links'][$entity_type]['metadata']['ds_bundle'] = $bundle;
      $build['#contextual_links'][$entity_type]['metadata']['ds_view_mode'] = $view_mode;
    }

    // If no layout is configured, stop executing.
    if (!$display->getThirdPartySetting('ds', 'layout')) {
      return;
    }

    // If Display Suite is disabled, stop here.
    if (Ds::isDisabled()) {
      return;
    }

    // Load field permissions and layouts only once.
    if (!$loaded) {
      $loaded = TRUE;
      $field_permissions = \Drupal::config('ds_extras.settings')->get('field_permissions');
    }

    // Get configuration.
    $manage_display_settings = $display->getThirdPartySettings('ds');

    // Don't fatal on missing layout plugins.
    $layout_id = $manage_display_settings['layout']['id'] ?? '';
    if (!Ds::layoutExists($layout_id)) {
      return;
    }

    /** @var \Drupal\Core\Layout\LayoutInterface $layout */
    $layout_settings = $manage_display_settings['layout']['settings'];

    // Add entity and view mode to settings.
    $layout_settings['_ds_entity'] = $entity;
    $layout_settings['_ds_view_mode'] = $view_mode;

    // Get the layout.
    $layout = \Drupal::service('plugin.manager.core.layout')->createInstance($manage_display_settings['layout']['id'], $layout_settings);

    $layout_build = $layout->build($manage_display_settings['regions']);
    $build['#ds_variables'] = $layout_build;
    $build['#layout'] = $layout_build['#layout'];
    $build['#settings'] = $layout_build['#settings'];
    $build['#theme'] = $layout_build['#theme'];

    // For some reason, #entity_type is not always set.
    if (!isset($build['#entity_type'])) {
      $build['#entity_type'] = $entity->getEntityTypeId();
    }

    $this->addDsFields($build, $entity, $bundle, $view_mode, $display, $manage_display_settings, $field_permissions);
    $this->implementUiLimit($build, $display);
    $this->buildFieldGroups($build, $entity, $display);
    $this->moveFieldsIntoRegions($build, $manage_display_settings);
  }

  /**
   * Add DS fields to the entity build.
   *
   * @param $build
   * @param $entity
   * @param $bundle
   * @param $view_mode
   * @param $display
   * @param $configuration
   * @param $field_permissions
   */
  protected function addDsFields(&$build, $entity, $bundle, $view_mode, $display, $configuration, $field_permissions) {
    $entity_type = $entity->getEntityTypeId();

    // Add Display Suite fields.
    $fields = Ds::getFields($entity_type);
    $field_values = !empty($configuration['fields']) ? $configuration['fields'] : [];

    foreach ($configuration['regions'] as $region) {
      foreach ($region as $weight => $key) {
        // Ignore if this field is not a DS field, just pull it in from the
        // entity.
        if (!isset($fields[$key])) {
          continue;
        }

        $field = $fields[$key];
        if (isset($field_values[$key]['formatter'])) {
          $field['formatter'] = $field_values[$key]['formatter'];
        }

        if (isset($field_values[$key]['settings'])) {
          $field['settings'] = $field_values[$key]['settings'];
        }

        $field_instance = Ds::getFieldInstance($key, $field, $entity, $view_mode, $display, $build);
        $field_value = $field_instance->build();
        $field_title = $field_instance->getTitle();

        // If the field value is cache data then we presume the value was empty
        // and we just have cache data as to why it's empty.
        if ($field_value instanceof CacheableMetadata) {
          CacheableMetadata::createFromRenderArray($build)
            ->merge($field_value)
            ->applyTo($build);
        }
        // Only allow non empty fields.
        elseif (!empty($field_value)) {
          $build[$key] = [
            '#theme' => 'field',
            '#field_type' => 'ds',
            '#title' => $field_title,
            '#weight' => $field_values[$key]['weight'] ?? $weight,
            '#label_display' => $field_values[$key]['label'] ?? 'inline',
            '#field_name' => $key,
            '#bundle' => $bundle,
            '#object' => $entity,
            '#entity_type' => $entity_type,
            '#view_mode' => '_custom',
            '#ds_view_mode' => $view_mode,
            '#items' => [(object) ['_attributes' => []]],
            '#is_multiple' => $field_instance->isMultiple(),
            '#access' => !($field_permissions && function_exists('ds_extras_ds_field_access')) || ds_extras_ds_field_access($key, $entity_type),
            '#formatter' => 'ds_field',
          ];

          if ($field_instance->isMultiple()) {
            $build[$key] += $field_value;
          }
          else {
            $build[$key][0] = [$field_value];
          }
        }
      }
    }
  }

  /**
   * Implement UI limit.
   *
   * @param $build
   * @param \Drupal\Core\Entity\Display\EntityDisplayInterface $display
   */
  protected function implementUILimit(&$build, EntityDisplayInterface $display) {
    // Implement UI limit.
    $components = $display->getComponents();
    foreach ($components as $field => $component) {
      if (isset($component['third_party_settings']['ds']) && !empty($component['third_party_settings']['ds']['ds_limit'])) {
        $limit = $component['third_party_settings']['ds']['ds_limit'];
        if (isset($build[$field]) && isset($build[$field]['#items'])) {
          if ($limit === 'delta' && isset($build['#ds_delta']) && isset($build['#ds_delta'][$field])) {

            // Get delta.
            $delta = $build['#ds_delta'][$field];

            // Remove caching for this entity as it otherwise won't work.
            unset($build['#cache']);

            $filtered_elements = Element::children($build[$field]);
            foreach ($filtered_elements as $filtered_element) {
              if ($filtered_element != $delta) {
                unset($build[$field][$filtered_element]);
              }
            }
          }
          elseif (is_numeric($limit)) {

            // Remove caching for this entity as it otherwise won't work.
            unset($build['#cache']);

            $filtered_elements = Element::children($build[$field]);
            $filtered_elements = array_slice($filtered_elements, $limit);
            foreach ($filtered_elements as $filtered_element) {
              unset($build[$field][$filtered_element]);
            }
          }
        }
      }
    }
  }

  /**
   * Move fields into the layout regions.
   *
   * @param $build
   * @param $configuration
   */
  protected function moveFieldsIntoRegions(&$build, $configuration) {
    $use_field_names = \Drupal::config('ds.settings')->get('use_field_names');

    foreach (array_keys($configuration['regions']) as $region_name) {
      $build[$region_name] = [];

      // Create the region content.
      if (!empty($configuration['regions'][$region_name])) {
        foreach ($configuration['regions'][$region_name] as $key => $field) {
          // Make sure the field exists.
          if (!isset($build[$field])) {
            continue;
          }

          // Always set weight.
          $build[$field]['#weight'] = $key;

          if ($use_field_names) {
            $build[$region_name][$field] = $build[$field];
          }
          else {
            $build[$region_name][$key] = $build[$field];
          }
          unset($build[$field]);
        }
      }
    }
  }

  /**
   * Build Field Groups, if any.
   *
   * @param $build
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param \Drupal\Core\Entity\Display\EntityDisplayInterface $display
   */
  protected function buildFieldGroups(&$build, EntityInterface $entity, EntityDisplayInterface $display) {
    if (\Drupal::moduleHandler()->moduleExists('field_group')) {

      // There's a chance field_attach_groups hasn't been called yet. Not 100%
      // sure why that happens, but might be due to the following core bug:
      // https://www.drupal.org/project/drupal/issues/3120298
      // Manual testing is fine, but FieldGroupTest didn't work at all, so
      // we manually call it here. It's wrapped in a setting where the default
      // value comes from \Drupal::state('ds_call_field_group_attach');
      // which returns FALSE by default, but is set to TRUE in the test.
      $ds_call_field_group_attach_default = \Drupal::state()->get('ds_call_field_group_attach', FALSE);
      if (Settings::get('ds_call_field_group_attach', $ds_call_field_group_attach_default)) {
        field_group_entity_view_alter($build, $entity, $display);
      }

      field_group_build_entity_groups($build);
    }
  }

}