<?php

namespace Drupal\cl_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Log\LoggerInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "individual_cupping",
 *   label = @Translation("Individual cupping"),
 *   uri_paths = {
 *     "https://www.drupal.org/link-relations/create" = "/api/individual-cupping",
 *   }
 * )
 */
class IndividualCupping extends ResourceBase {

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
   * Constructs a BrewingMethod object.
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
   * Responds to post requests.
   *
   * @param array $data
   *   Individual cupping data.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $data) {
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access individual cupping resource post')) {
      throw new AccessDeniedHttpException();
    }
    else {
      $sample_name = '';
      $session_id = '';
      $title = '';
      $fragance = $flavour = $aftertaste = $acidity = $body = $balance = $overall = 0;
      $uniformity = $clean_cup = $sweetness = 0;
      $uniformity_data = $clean_cup_data = $sweetness_data = '';
      $defect_cups = 0;
      $defects_intensity = NULL;
      $notes = '';

      $session_node = NULL;

      if (isset($data['session_id'])) {
        $session_id = $data['session_id'];
        $session_node = $this->getCuppingSession($session_id);

      }
      if (!$session_node) {
        throw new BadRequestHttpException('Incorrect cupping session data.');
      }

      if (isset($data['sample_name'])) {
        foreach ($session_node->field_sample_names as $session_sample_name) {
          if ($session_sample_name->value === $data['sample_name']) {
            $sample_name = $data['sample_name'];
            break;
          }
        }
      }
      if (!$sample_name) {
        throw new BadRequestHttpException('Incorrect sample name data.');
      }

      $title = $this->currentUser->getDisplayName() . '-' . $session_id . '-' . $sample_name;

      $paragraph = Paragraph::create([
        'type' => 'cupping_sample',
        'field_acidity' => [
          'value' => isset($data['acidity']) ? $data['acidity'] : '6.0',
        ],
        'field_aftertaste' => [
          'value' => isset($data['aftertaste']) ? $data['aftertaste'] : '6.0',
        ],
        'field_balance' => [
          'value' => isset($data['balance']) ? $data['balance'] : '6.0',
        ],
        'field_body' => [
          'value' => isset($data['body']) ? $data['body'] : '6.0',
        ],
        'field_clean_cup' => [
          'value' => isset($data['clean_cup']) ? $data['clean_cup'] : '11111',
        ],
        'field_clean_cup_data' => [
          'value' => isset($data['clean_cup_data']) ? $data['clean_cup_data'] : '11111',
        ],
        'field_defect_cups' => [
          'value' => isset($data['defect_cups']) ? $data['defect_cups'] : '0',
        ],
        'field_defects_intensity' => [
          'value' => isset($data['defect_intensity']) ? $data['defect_intensity'] : '',
        ],
        'field_flavour' => [
          'value' => isset($data['flavour']) ? $data['flavour'] : '6.0',
        ],
        'field_fragance_aroma' => [
          'value' => isset($data['fragance']) ? $data['fragance'] : '6.0',
        ],
        'field_notes' => [
          'value' => isset($data['notes']) ? $data['notes'] : '',
        ],
        'field_overall' => [
          'value' => isset($data['overall']) ? $data['overall'] : '6.0',
        ],
        'field_sweetness' => [
          'value' => isset($data['sweetness']) ? $data['sweetness'] : '10',
        ],
        'field_sweetness_data' => [
          'value' => isset($data['sweetness_data']) ? $data['sweetness_data'] : '11111',
        ],
        'field_uniformity' => [
          'value' => isset($data['uniformity']) ? $data['uniformity'] : '10',
        ],
        'field_uniformity_data' => [
          'value' => isset($data['uniformity_data']) ? $data['uniformity_data'] : '11111',
        ],
      ]);
      $paragraph->save();

      $node = Node::create([
        'type'        => 'individual_cupping',
        'title'       => $title,
        'field_sample_name' => [
          'value' => $sample_name,
        ],
        'field_cupping_data' => [
          'target_id' => $paragraph->id(),
          'target_revision_id' => $paragraph->getRevisionId(),
        ],
      ]);
      $node->save();

      $session_node->field_individual_cuppings->appendItem($node);
      $session_node->save();

      return new ResourceResponse(['message' => 'Created successfully'], 201);
    }
  }

  /**
   * Get cupping session by id.
   */
  protected function getCuppingSession($session_id) {
    $query = $this->entityTypeManager->getStorage('node');
    $results = $query->getQuery()
      ->condition('status', 1)
      ->condition('type', 'cupping_session')
      ->condition('field_session_id', $session_id)
      ->execute();
    if (count($results)) {
      $result = reset($results);
      return $this->entityTypeManager->getStorage('node')->load($result);
    }
    return NULL;
  }

}
