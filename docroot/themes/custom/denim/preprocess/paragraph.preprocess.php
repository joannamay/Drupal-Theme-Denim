<?php

/**
 * @file
 * Preprocess functions related to paragraph entities.
 *
 * Index:
 *
 * @see denim_preprocess_paragraph()
 * @see denim_preprocess_paragraph__accordion__full()
 * @see denim_preprocess_paragraph__accordion_item__full()
 * @see denim_preprocess_paragraph__content__full()
 * @see denim_preprocess_paragraph__hero__full()
 * @see denim_preprocess_paragraph__hero_slide__full()
 * @see denim_preprocess_paragraph__embed_iframe__full()
 * @see denim_preprocess_paragraph__media__full()
 * @see denim_preprocess_paragraph__section__full()
 * @see denim_preprocess_paragraph__slider__full()
 * @see denim_preprocess_paragraph__tabs__full()
 * @see denim_preprocess_paragraph__tabs_tab__full()
 */

use Drupal\Component\Utility\Html;
use Drupal\Core\Url;

/**
 * Implements hook_preprocess_paragraph().
 */
function denim_preprocess_paragraph(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $bundle = $paragraph->bundle();
  $view_mode = $variables['view_mode'];
  $base_class = $variables['component_base_class'];

  // Initialize settings (this approach allows less IF/ELSE nesting).
  $setting_background_image_unwrap = FALSE;
  $setting_class_custom = FALSE;
  $setting_component_unwrap = FALSE;
  $setting_title_move = FALSE;

  // Toggle settings based on view-mode.
  switch ($variables['view_mode']) {
    case 'full':
      $setting_background_image_unwrap = TRUE;
      $setting_class_custom = TRUE;
      $setting_component_unwrap = TRUE;
      $setting_title_move = TRUE;
      break;
  }

  // Unset background-image field theme wrapper (to not print an empty div).
  if ($setting_background_image_unwrap && array_key_exists('field_background_image', $variables['content'])) {
    unset($variables['content']['field_background_image']);
  }

  // Add custom classes to the component.
  if ($setting_class_custom && $paragraph->hasField('field_class_custom') && !$paragraph->get('field_class_custom')->isEmpty()) {
    $field_class_custom = preg_replace('/[^a-zA-Z0-9\-_ ]/', '', $paragraph->get('field_class_custom')->value);
    $variables['attributes']['class'] = array_merge($variables['attributes']['class'], explode(' ', $field_class_custom));
  }

  // Remove component field-wrappers.
  if ($setting_component_unwrap && array_key_exists('field_components', $variables['content'])) {
    unset($variables['content']['field_components']['#theme']);
  }

  // Set title variable from fields.
  if ($setting_title_move) {
    foreach (['field_title_link', 'field_title'] as $title_fieldname) {
      if (array_key_exists($title_fieldname, $variables['content'])) {

        $variables['title'] = $variables['content'][$title_fieldname];

        unset($variables['title']['#theme']);
        unset($variables['content'][$title_fieldname]);
        break;
      }
    }
  }
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for accordion,
 * full.
 */
function denim_preprocess_paragraph__accordion__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Hide tab heading. This is rendered and visually hidden for accessibility.
  $variables['title_attributes']['class'][] = 'visually-hidden';
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for accordion_item,
 * full.
 */
function denim_preprocess_paragraph__accordion_item__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Check if this accordion item should be open by default.
  $is_open = (!$paragraph->get('field_enabled')->isEmpty() && $paragraph->get('field_enabled')->value === '1');
  if ($is_open) {
    $variables['attributes']['class'][] = "{$base_class}--open";
  }
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for content, full.
 */
function denim_preprocess_paragraph__content__full(array &$variables) {
  // Nothing to see here.
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for hero, full.
 */
function denim_preprocess_paragraph__hero__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Track whether the hero has slides.
  $variables['has_slides'] = !$paragraph->get('field_components')->isEmpty();
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for hero_slide,
 * full.
 */
function denim_preprocess_paragraph__hero_slide__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Initialize variables.
  $variables['inner_attributes']['class'][] = "{$base_class}__inner";

  // Move media field to new variable.
  $variables['media'] = $variables['content']['field_media_background'];
  unset($variables['media']['#theme']);
  unset($variables['content']['field_media_background']);

}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for embed_iframe,
 * full.
 */
function denim_preprocess_paragraph__embed_iframe__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Track that iframe_attributes should get converted.
  $variables['#attribute_variables'][] = 'iframe_attributes';

  // Clean src input and compile style tag.
  $src = $paragraph->get('field_link')->isEmpty() ?: $paragraph->get('field_link')->uri;
  $style_tag = '';
  $height = '600px';
  if (!$paragraph->get('field_style_height')->isEmpty()) {
    $height = Html::escape($paragraph->get('field_style_height')->value);
    $style_tag .= "height: {$height}; ";
  }
  $width = '100%';
  if (!$paragraph->get('field_style_width')->isEmpty()) {
    $width = Html::escape($paragraph->get('field_style_width')->value);
    $style_tag .= "height: {$height}; ";
  }

  // Set attributes.
  $variables['iframe_attributes']['class'][] = "{$base_class}__iframe";
  $variables['iframe_attributes']['height'] = $height;
  $variables['iframe_attributes']['src'] = $src;
  $variables['iframe_attributes']['style'] = $style_tag;
  $variables['iframe_attributes']['width'] = $width;
}

/**
 * Implements hook_preprocess_paragraph__VIEW_MODE() for media, full.
 */
function denim_preprocess_paragraph__media__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Attach additional libraries based on the current layout style.
  if (!$paragraph->get('field_media_layout')->isEmpty()) {
    switch($paragraph->get('field_media_layout')->target_id) {
      case 'media_layout__full':
        $variables['#attached']['library'][] = 'denim/paragraph--full--media--layout-full';
        break;

      case 'media_layout__grid':
        $variables['#attached']['library'][] = 'denim/paragraph--full--media--layout-grid';
        break;

      case 'media_layout__masonry':
        $variables['#attached']['library'][] = 'denim/paragraph--full--media--layout-masonry';
        break;

      case 'media_layout__slider':
        $variables['#attached']['library'][] = 'denim/paragraph--full--media--layout-slider';
        break;
    }
  }

  // Unset media field theme wrapper.
  unset($variables['content']['field_media']['#theme']);
}

/**
 * Implements hook_preprocess_paragraph__VIEW_MODE() for section, full.
 */
function denim_preprocess_paragraph__section__full(array &$variables) {
  // Nothing to see here.
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for slider, full.
 */
function denim_preprocess_paragraph__slider__full(array &$variables) {
  // Nothing to see here.
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for tabs, full.
 */
function denim_preprocess_paragraph__tabs__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Hide tab heading. This is rendered and visually hidden for accessibility.
  $variables['title_attributes']['class'][] = 'visually-hidden';

  // Initialize Navigation variable.
  $variables['nav']['#attributes']['class'][] = "{$base_class}__nav";
  $variables['nav']['#wrapper_attributes'] = [];
  $variables['nav']['#items'] = [];
  $variables['nav']['#theme'] = 'item_list';

  // Validate and create nav from tab list.
  foreach ($paragraph->get('field_components') as $delta => $component) {
    /** @var \Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem $component */

    // Add tab to nav if title is set.
    if ($component->entity instanceof \Drupal\paragraphs\ParagraphInterface && !$component->entity->get('field_title')->isEmpty()) {
      $variables['nav']['#items'][] = [
        '#type' => 'link',
        '#title' => $component->entity->get('field_title')->value,
        '#url' => Url::fromUserInput("#paragraph-{$component->entity->id()}"),
        '#wrapper_attributes' => [
          'class' => ["{$base_class}__nav-item"],
        ],
      ];
    }
    // Otherwise remove it from being rendered.
    else {
      unset($variables['content']['field_components'][$delta]);
    }
  }

  // Toggle version of tabs. (This is done here mainly as a placeholder for if
  // we ever need to support vertical tabs or other version. Then we can make it
  // toggleable and just add the class.)
  $tab_version = 'horizontal';
  switch($tab_version) {
    case 'horizontal':
    default:
      $variables['attributes']['class'][] = "{$base_class}--horizontal";
  }
}

/**
 * Implements hook_preprocess_paragraph__BUNDLE__VIEW_MODE() for tabs_tab, full.
 */
function denim_preprocess_paragraph__tabs_tab__full(array &$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $base_class = $variables['component_base_class'];

  // Default to tabindex -1 for accessibility. JS will initialize and update.
  $variables['attributes']['tabindex'] = '-1';
  // Hide tab heading. This is rendered and visually hidden for accessibility.
  $variables['title_attributes']['class'][] = 'visually-hidden';
}
