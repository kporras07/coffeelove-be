<?php

namespace Drupal\cl_api\Helpers;

/**
 * Helper for brewing methods.
 */
class BrewingMethodsHelper {

  /**
   * Format brewing method.
   */
  public static function formatBrewingMethod($taxonomy_term) {
    $data = [
      'title' => $taxonomy_term->label(),
      'id' => $taxonomy_term->id(),
    ];
    return $data;
  }

}
