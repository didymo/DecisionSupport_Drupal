<?php

declare(strict_types=1);

namespace Drupal\decision_support_file\Plugin\rest\resource;

use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Route;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\decision_support_file\Entity\DecisionSupportFile;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\decision_support_file\Services\DecisionSupportFile\DecisionSupportFileService;

/**
 * Represents Decision Support File Archive records as resources.
 *
 * @RestResource (
 *   id = "archive_decision_support_file",
 *   label = @Translation("Decision Support File Archive"),
 *   uri_paths = {
 *     "canonical" = "/rest/support/file/archive/{fileId}",
 *     "patch" = "/rest/support/file/archive/{fileId}"
 *   }
 * )
 *
 * @DCG
 * The plugin exposes key-value records as REST resources. In order to enable it
 * import the resource configuration into active configuration storage. An
 * example of such configuration can be located in the following file:
 * core/modules/rest/config/optional/rest.resource.entity.node.yml.
 * Alternatively, you can enable it through admin interface provider by REST UI
 * module.
 * @see https://www.drupal.org/project/restui
 *
 * @DCG
 * Notice that this plugin does not provide any validation for the data.
 * Consider creating custom normalizer to validate and normalize the incoming
 * data. It can be enabled in the plugin definition as follows.
 * @code
 *   serialization_class = "Drupal\foo\MyDataStructure",
 * @endcode
 *
 * @DCG
 * For entities, it is recommended to use REST resource plugin provided by
 * Drupal core.
 * @see \Drupal\rest\Plugin\rest\resource\EntityResource
 */
final class ArchiveDecisionSupportFileResource extends ResourceBase {

  /**
   * The key-value storage.
   */
  private readonly KeyValueStoreInterface $storage;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    KeyValueFactoryInterface $keyValueFactory,
    AccountProxyInterface    $currentUser,
    DecisionSupportFileService $decision_support_service
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->storage = $keyValueFactory->get('archive_decision_support');
    $this->currentUser = $currentUser;
    $this->decisionSupportFileService = $decision_support_service;
    
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('keyvalue'),
      $container->get('current_user'),
      $container->get('decision_support_file.service')

    );
  }

 

  /**
   * Responds to PATCH requests.
   */
  public function patch($fileId): ModifiedResourceResponse {

        // Check user permissions.
        if (!$this->currentUser->hasPermission('access content')) {
          throw new AccessDeniedHttpException();
        }
    
        try {
         // Attempt to update the decision support file entity.
         $entity = $this->decisionSupportFileService->deleteDecisionSupportFile($fileId);
         $this->logger->notice('The Decision Support File @id has been moved to Archived.', ['@id' => $fileId]);
         
         // Return a response with status code 200 OK.
         return new ModifiedResourceResponse($entity, 200);
       } 
       catch (\Exception $e) {
         // Handle any other exceptions that occur during moving entity to archived.
         $this->logger->error('An error occurred while moving Decision Support File to archived: @message', ['@message' => $e->getMessage()]);
         throw new HttpException(500, 'Internal Server Error');
       }

  }
}