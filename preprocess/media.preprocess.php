<?php

/**
 * @file
 * Preprocess functions related to media entities.
 *
 * Index:
 * @see denim_preprocess_media()
 * @see denim_preprocess_media__remote_video__hero()
 */

/**
 * Implements hook_preprocess_media().
 */
function denim_preprocess_media(array &$variables) {
  /** @var \Drupal\media\MediaInterface $media */
  $media = $variables['media'];


  // Track that iframe_attributes should get converted.
  $variables['#attribute_variables'][] = 'media_attributes';

  // Add base classes.
  $variables['media_attributes']['class'][] = 'media__media';

  // Move respective media field into its own variable.
  $field_name = NULL;
  switch($media->bundle()) {
    case 'audio':
      $field_name = 'field_media_audio_file';
      break;

    case 'file':
      $field_name = 'field_media_file';
      break;

    case 'image':
      $field_name = 'field_media_image';
      break;

    case 'remote_video':
      $field_name = 'field_media_video_embed_field';
      break;

    case 'video':
      $field_name = 'field_media_video_file';
      break;
  }
  if (!is_null($field_name) && array_key_exists($field_name, $variables['content'])) {
    $variables['media_embed'] = $variables['content'][$field_name];
    unset($variables['media_embed']['#theme']);
    unset($variables['content'][$field_name]);
  }

}

/**
 * Implements hook_preprocess_media__BUNDLE__VIEW_MODE() for remote_video, hero.
 */
function denim_preprocess_media__remote_video__hero(array &$variables) {
  /** @var \Drupal\media\MediaInterface $media */
  $media = $variables['media'];

  // Make video autoplay, loop and disabling any controls and branding.
  if (isset($variables['media_embed'][0]['children']['#provider'])) {
    // Get provider information.
    $provider = $variables['media_embed'][0]['children']['#provider'];
    $provider_definition = \Drupal::service('video_embed_field.provider_manager')->getDefinition($provider);
    $provider_class = $provider_definition['class'];
    $provider_id = $provider_class::getIdFromInput($variables['media_embed']['#items']->first()->value);

    // Make modifications to the embed based on provider.
    switch ($provider) {
      case 'vimeo':
        $variables['media_embed'][0]['children']['#attributes']['title'] = $media->label();
        $variables['media_embed'][0]['children']['#query']['autoplay'] = 1;
        $variables['media_embed'][0]['children']['#query']['background'] = 1;
        $variables['media_embed'][0]['children']['#query']['loop'] = 1;
        $variables['media_embed'][0]['children']['#query']['muted'] = 1;
        $variables['media_embed'][0]['children']['#query']['api'] = 1;
        break;

      case 'youtube':
        $variables['media_embed'][0]['children']['#attributes']['title'] = $media->label();
        $variables['media_embed'][0]['children']['#attributes']['tabindex'] = '-1';
        $variables['media_embed'][0]['children']['#query']['autoplay'] = 1;
        $variables['media_embed'][0]['children']['#query']['showinfo'] = 0;
        $variables['media_embed'][0]['children']['#query']['controls'] = 0;
        $variables['media_embed'][0]['children']['#query']['mute'] = 1;
        $variables['media_embed'][0]['children']['#query']['disablekb'] = 1;
        $variables['media_embed'][0]['children']['#query']['fs'] = 0;
        $variables['media_embed'][0]['children']['#query']['mute'] = 1;
        $variables['media_embed'][0]['children']['#query']['loop'] = 1;
        $variables['media_embed'][0]['children']['#query']['modestbranding'] = 1;
        $variables['media_embed'][0]['children']['#query']['playlist'] = $provider_id;
        $variables['media_embed'][0]['children']['#query']['enablejsapi'] = 1;
        break;
    }
  }
}
