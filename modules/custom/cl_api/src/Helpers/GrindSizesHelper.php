<?php

namespace Drupal\cl_api\Helpers;

/**
 * Helper for grind sizes.
 */
class GrindSizesHelper {

  /**
   * Format brewing method.
   */
  public static function formatGrindSizes($taxonomy_term) {
    $data = [
      'id' => $taxonomy_term->id(),
      'title' => $taxonomy_term->label(),
      'description' => $taxonomy_term->description->value,
    ];
    return $data;
  }

}
