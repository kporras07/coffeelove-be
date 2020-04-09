<?php

namespace Drupal\cl_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get cupping sessions.
 *
 * @RestResource(
 *   id = "cupping_session",
 *   label = @Translation("Cupping Session"),
 *   uri_paths = {
 *     "canonical" = "/api/cupping-session/{id}",
 *   }
 * )
 */
class CuppingSession extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Entity type manager interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a CuppingSession object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current user account proxy.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('cl_api'),
      $container->get('current_user'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @param string $id
   *   Cupping session id.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get($id = NULL) {
    $node = NULL;
    $query = $this->entityTypeManager->getStorage('node');
    $results = $query->getQuery()
      ->condition('status', 1)
      ->condition('type', 'cupping_session')
      ->condition('field_session_id', $id)
      ->execute();
    if (count($results)) {
      $result = reset($results);
      $node = $this->entityTypeManager->getStorage('node')->load($result);
    }

    if (!$node) {
      throw new NotFoundHttpException('Cupping Session not found');
    }

    $data = [
      'title' => $node->get('title'),
      'description' => $node->get('field_session_description'),
      'session_id' => $node->get('field_session_id'),
      'cups_per_sample' => $node->get('field_cups_per_sample'),
      'sample_names' => $node->get('field_sample_names'),
    ];
    return new ResourceResponse($data, 200);
  }

}
