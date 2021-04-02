<?php

namespace Drupal\clc_web_services\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Define a route controller for entity autocomplete form elements.
 */
class AutocompleteController extends ControllerBase {

  /**
   * Handler for contents request.
   */
  public function handleContents(Request $request) {
    $results = [];
    // Get the typed string from the URL, if it exists.
    if ($input = $request->query->get('q')) {
      $nids = \Drupal::entityQuery('node')
        ->condition('status', TRUE)
        ->condition('type', 'page')
        ->condition('title', "%{$input}%", 'LIKE')
        ->execute();
      $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);

      foreach ($nodes as $key => $node) {
        $nid = $node->id();
        $node_title = $node->title->getValue();
        $node_title = reset($node_title);

        $results[] = "{$node_title['value']} ({$nid})";
      }
    }

    return new Jsonresponse($results);
  }
}
