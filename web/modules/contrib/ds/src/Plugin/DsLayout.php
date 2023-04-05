<?php

namespace Drupal\ds\Plugin;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\Display\EntityDisplayInterface;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\ds\Ds;
use Drupal\Core\Link;

/**
 * Layout class for all Display Suite layouts.
 */
class DsLayout extends LayoutDefault implements PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'wrappers' => [],
      'entity_classes' => 'all_classes',
      'disable_css' => FALSE,
      'outer_wrapper' => 'div',
      'attributes' => '',
      'link_attribute' => '',
      'link_custom' => '',
      'classes' => [
        'layout_class' => [],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $configuration = $this->getConfiguration();
    $regions = $this->getPluginDefinition()->getRegions();

    // Add wrappers.
    $wrapper_options = [
      'div' => 'Div',
      'span' => 'Span',
      'section' => 'Section',
      'article' => 'Article',
      'header' => 'Header',
      'footer' => 'Footer',
      'aside' => 'Aside',
      'figure' => 'Figure',
    ];
    $form['layout_options'] = [
      '#group' => 'additional_settings',
      '#type' => 'details',
      '#title' => $this->t('Layout settings'),
      '#tree' => TRUE,
    ];

    $form['layout_options']['disable_css'] = array(
      '#type' => 'checkbox',
      '#title' => t('Disable layout CSS styles'),
      '#default_value' => $configuration['disable_css'],
    );

    // Default classes.
    $form['layout_options']['entity_classes'] = [
      '#type' => 'select',
      '#title' => t('Entity classes'),
      '#options' => [
        'all_classes' => t('Entity, bundle and view mode'),
        'no_classes' => t('No classes'),
      ],
      '#default_value' => $configuration['entity_classes'],
    ];

    foreach ($regions as $region_name => $region_definition) {
      $form['layout_options'][$region_name] = [
        '#type' => 'select',
        '#options' => $wrapper_options,
        '#title' => $this->t('Wrapper for @region', ['@region' => $region_definition['label']]),
        '#default_value' => !empty($configuration['wrappers'][$region_name]) ? $configuration['wrappers'][$region_name] : 'div',
      ];
    }

    $form['layout_options']['outer_wrapper'] = [
      '#type' => 'select',
      '#options' => $wrapper_options,
      '#title' => $this->t('Outer wrapper'),
      '#default_value' => $configuration['outer_wrapper'],
      '#weight' => 10,
    ];

    $form['layout_options']['attributes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Layout attributes'),
      '#description' => $this->t('E.g. role|navigation,data-something|some value'),
      '#default_value' => $configuration['attributes'],
      '#weight' => 11,
    ];

    $form['layout_options']['link_attribute'] = [
      '#type' => 'select',
      '#options' => [
        '' => $this->t('No link'),
        'content' => $this->t('Link to content'),
        'custom' => $this->t('Custom'),
        'tokens' => $this->t('Tokens'),
      ],
      '#title' => $this->t('Add link'),
      '#description' => $this->t('This will add an onclick attribute on the layout wrapper.'),
      '#default_value' => $configuration['link_attribute'],
      '#weight' => 12,
    ];

    $form['layout_options']['link_custom'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom link'),
      '#description' => $this->t('You may use tokens for this link if you selected tokens.'),
      '#default_value' => $configuration['link_custom'],
      '#weight' => 13,
      '#states' => [
        'visible' => [
          [
            ':input[name="layout_configuration[layout_options][link_attribute]"]' => [["value" => "tokens"], ["value" => "custom"]],
          ],
        ],
      ],
    ];

    if (\Drupal::moduleHandler()->moduleExists('token')) {
      $form['layout_options']['tokens'] = [
        '#title' => $this->t('Tokens'),
        '#type' => 'container',
        '#weight' => 14,
        '#states' => [
          'visible' => [
            ':input[name="layout_configuration[layout_options][link_attribute]"]' => ["value" => "tokens"],
          ],
        ],
      ];

      $token_types = 'all';
      // The entity is not always available.
      // See https://www.drupal.org/project/ds/issues/3137198.
      if (($form_object = $form_state->getFormObject()) && $form_object instanceof EntityFormInterface && ($entity = $form_object->getEntity()) && $entity instanceof EntityDisplayInterface) {
        $token_types = [$entity->getTargetEntityTypeId()];
      }

      $form['layout_options']['tokens']['help'] = [
        '#theme' => 'token_tree_link',
        '#token_types' => $token_types,
        '#global_types' => TRUE,
        '#dialog' => TRUE,
      ];
    }

    // Add extra classes for the regions to have more control while theming.
    $form['ds_classes'] = [
      '#group' => 'additional_settings',
      '#type' => 'details',
      '#title' => $this->t('Custom classes'),
      '#tree' => TRUE,
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];

    $classes_access = (\Drupal::currentUser()->hasPermission('admin_classes'));
    $classes = Ds::getClasses();
    if (!empty($classes)) {
      $layoutSettings = $this->getPluginDefinition()->get('settings') ?: [];

      $default_layout_classes = $layoutSettings['classes']['layout_class'] ?? [];
      $form['ds_classes']['layout_class'] = [
        '#type' => 'select',
        '#multiple' => TRUE,
        '#options' => $classes,
        '#title' => $this->t('Class for layout'),
        '#default_value' => !empty($configuration['classes']['layout_class']) ? $configuration['classes']['layout_class'] : $default_layout_classes,
      ];

      foreach ($regions as $region_name => $region_definition) {
        $default_classes = $layoutSettings['classes'][$region_name] ?? [];
        $form['ds_classes'][$region_name] = [
          '#type' => 'select',
          '#multiple' => TRUE,
          '#options' => $classes,
          '#title' => $this->t('Class for @region', ['@region' => $region_definition['label']]),
          '#default_value' => $configuration['classes'][$region_name] ?? $default_classes,
        ];
      }
      if ($classes_access) {
        $url = Url::fromRoute('ds.classes');
        $destination = \Drupal::destination()->getAsArray();
        $url->setOption('query', $destination);
        $form['ds_classes']['info'] = ['#markup' => Link::fromTextAndUrl($this->t('Manage region and field CSS classes'), $url)->toString()];
      }
    }
    else {
      if ($classes_access) {
        $url = Url::fromRoute('ds.classes');
        $destination = \Drupal::destination()->getAsArray();
        $url->setOption('query', $destination);
        $form['ds_classes']['info'] = ['#markup' => '<p>' . $this->t('You have not defined any CSS classes which can be used on regions.') . '</p><p>' .  Link::fromTextAndUrl($this->t('Manage region and field CSS classes'), $url)->toString() . '</p>'];
      }
      else {
        $form['ds_classes']['#access'] = FALSE;
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['wrappers'] = $form_state->getValue('layout_options');
    foreach (['outer_wrapper', 'attributes', 'link_attribute', 'link_custom', 'disable_css', 'entity_classes'] as $name) {
      $this->configuration[$name] = $this->configuration['wrappers'][$name];
      unset($this->configuration['wrappers'][$name]);
    }

    // Apply Xss::filter to attributes.
    $this->configuration['attributes'] = Xss::filter($this->configuration['attributes']);

    // In case classes is missing entirely, use the defaults.
    $defaults = $this->defaultConfiguration();
    $this->configuration['classes'] = $form_state->getValue('ds_classes', $defaults['classes']);

    // Do not save empty classes.
    foreach ($this->configuration['classes'] as &$classes) {
      foreach ($classes as $class) {
        if (empty($class)) {
          unset($classes[$class]);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $layout_settings = $this->getConfiguration();

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $layout_settings['_ds_entity'] ?? FALSE;
    $view_mode = $layout_settings['_ds_view_mode'] ?? 'full';

    unset($layout_settings['_ds_entity']);
    unset($layout_settings['_ds_view_mode']);
    $this->setConfiguration($layout_settings);

    // Parent build.
    $ds_build = parent::build($regions);

    // -------------------------------------------------------
    // Disable CSS files if configured.

    if ($layout_settings['disable_css']) {
      $ds_build['#attached']['library'] = [];
    }

    // -------------------------------------------------------
    // Create region variables based on the layout settings.

    if (!empty($layout_settings['wrappers']) && is_array($layout_settings['wrappers'])) {
      foreach ($layout_settings['wrappers'] as $region_name => $wrapper) {
        if (!empty($layout_settings['classes'][$region_name])) {
          $ds_build[$region_name . '_attributes'] = new Attribute(['class' => $layout_settings['classes'][$region_name]]);
        }
        else {
          $ds_build[$region_name . '_attributes'] = new Attribute();
        }
        $ds_build[$region_name . '_wrapper'] = !empty($layout_settings['wrappers'][$region_name]) ? $layout_settings['wrappers'][$region_name] : 'div';
      }
    }

    // -------------------------------------------------------
    // Attributes and classes

    // Entity classes.
    if ($layout_settings['entity_classes'] == 'all_classes' && $entity) {
      $ds_build['attributes']['class'][] = Html::cleanCssIdentifier($entity->getEntityTypeId());
      $ds_build['attributes']['class'][] = Html::cleanCssIdentifier($entity->getEntityTypeId()) . '--type-' . Html::cleanCssIdentifier($entity->bundle());
      $ds_build['attributes']['class'][] = Html::cleanCssIdentifier($entity->getEntityTypeId()) . '--view-mode-' . Html::cleanCssIdentifier($view_mode);
    }

    // Wrapper classes.
    if (!empty($layout_settings['classes']['layout_class'])) {
      foreach ($layout_settings['classes']['layout_class'] as $layout_class) {
        $ds_build['attributes']['class'][] = $layout_class;
      }
    }

    // Custom attributes.
    if (!empty($layout_settings['attributes'])) {
      $layout_attributes = explode(',', $layout_settings['attributes']);
      foreach ($layout_attributes as $layout_attribute) {
        $replaced_attribute = $layout_attribute;
        if (strpos($layout_attribute, '|') !== FALSE) {
          if (isset($entity_type_id) && isset($variables['content']['#entity_type'])) {
            $replaced_attribute = \Drupal::service('token')->replace(
              $layout_attribute,
              [$variables['content']['#entity_type'] => $variables['content']['#' . $entity_type_id]],
              ['clear' => TRUE]
            );
          }
          [$key, $attribute_value] = explode('|', $replaced_attribute);
          // Handle the class attribute as an array and others as strings.
          $key == 'class' ? $ds_build['attributes'][$key][] = $attribute_value : $ds_build['attributes'][$key] = $attribute_value;
        }
      }
    }


    // -------------------------------------------------------
    // Add a layout wrapper.

    $ds_build['outer_wrapper'] = $layout_settings['outer_wrapper'] ?? 'div';

    // -------------------------------------------------------
    // Add an onclick attribute on the wrapper.

    if (!empty($layout_settings['link_attribute'])) {
      $url = '';
      switch ($layout_settings['link_attribute']) {
        case 'content':
          if ($entity) {
            $url = $entity->toUrl()->getInternalPath();
          }
          break;

        case 'custom':
          $url = $layout_settings['link_custom'];
          break;

        case 'tokens':
          if ($entity) {
            $url = \Drupal::service('token')->replace($layout_settings['link_custom'], [$entity->getEntityTypeId() => $entity], ['clear' => TRUE]);
          }
          break;
      }

      if (!empty($url)) {
        $uri_parts = parse_url($url);
        if (empty($uri_parts['scheme'])) {
          $url = 'internal:/' . ltrim($url,'/');
        }

        $url = Url::fromUri($url);
        // Give the possibility to alter the url object.
        \Drupal::moduleHandler()->alter('ds_onclick_url', $url);
        $ds_build['attributes']['onclick'] = 'location.href=\'' . $url->toString() . '\'';
      }
    }

    return $ds_build;
  }

}
