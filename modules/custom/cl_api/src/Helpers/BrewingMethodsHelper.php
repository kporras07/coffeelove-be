<?php

namespace Drupal\cl_api\Helpers;

use Drupal\image\Entity\ImageStyle;

/**
 * Helper for brewing methods.
 */
class BrewingMethodsHelper {

  /**
   * Format brewing method.
   */
  public static function formatBrewingMethod($taxonomy_term) {
    $image_url = '';
    $thumbnail_url = '';
    if (!empty($taxonomy_term->field_image->entity->field_media_image->entity->uri->value)) {
      $style = ImageStyle::load('thumbnail');
      $uri = $taxonomy_term->field_image->entity->field_media_image->entity->uri->value;
      $image_url = file_create_url($uri);
      $thumbnail_url = $style->buildUrl($uri);
    }
    $data = [
      'id' => $taxonomy_term->id(),
      'title' => $taxonomy_term->label(),
      'description' => $taxonomy_term->description->value,
      'image' => $image_url,
      'thumbnail' => $thumbnail_url,
    ];
    return $data;
  }

}
