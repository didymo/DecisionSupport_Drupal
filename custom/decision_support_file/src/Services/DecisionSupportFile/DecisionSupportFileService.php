<?php

declare(strict_types=1);

namespace Drupal\decision_support_file\Services\DecisionSupportFile;

use Drupal\decision_support_file\Entity\DecisionSupportFile;
use Drupal\file\Entity\File;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @todo Service for Decision Support Files
 */
final class DecisionSupportFileService implements DecisionSupportFileServiceInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * Constructs a ProcessService object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this ->logger = $logger;
  }


  /**
   * {@inheritdoc}
   */
  public function getDecisionSupportFile($decisionSupportId){
    // Create an entity query for the decision_support_file entity.
    $query = $this->entityTypeManager
      ->getStorage('decision_support_file') // Get the storage handler.
      ->getQuery() // Create the query.
      ->condition('status', 1) // Add condition for published files.
      ->condition('decisionSupportId', $decisionSupportId) // Add condition for matching decisionSupportId.
     ->accessCheck(TRUE); // Enable access checks.

    
    $decisionSupportFileIds = $query->execute();
    $this->logger->info('Query result for Decision Support Id: {id}, File IDs: {file_ids}', [
      'id' => $decisionSupportId,
      'file_ids' => json_encode($decisionSupportFileIds)
    ]);
    $unformattedDecisionSupportFile = DecisionSupportFile::loadMultiple($decisionSupportFileIds);
    $decisionSupportFileList = array();

        foreach ($unformattedDecisionSupportFile as $unformattedDecisionSupportFile) {
          if ($unformattedDecisionSupportFile instanceof DecisionSupportFile) {
            $file['label'] = $unformattedDecisionSupportFile->getLabel();
            $file['entityId'] = $unformattedDecisionSupportFile->id();
            $file['stepId'] = $unformattedDecisionSupportFile->getStepId();
            $file['fileEntityId'] = $unformattedDecisionSupportFile->getFileId();
            $file['isVisible'] = $unformattedDecisionSupportFile->getVisible();

            $decisionSupportFileList[] = $file;
            unset($file);
          }
        }

        return $decisionSupportFileList;
  }

  /**
   * {@inheritdoc}
   */
  public function createDecisionSupportFile(array $data){
    // Validate required fields.
    if (empty($data['stepId']) || empty($data['decisionSupportId']) || empty($data['fid'])) {
      throw new BadRequestHttpException('Missing required fields');
    }

    // Load the file entity using the provided fid.
    $file_entity = File::load($data['fid']);
    if (!$file_entity) {
      throw new NotFoundHttpException('File not found');
    }

    // Create new DecisionSupportFile entity.
    $decision_support_file = DecisionSupportFile::create([
      'label' => $data['label'] ?? $file_entity->getFilename(),
      'notes' => $data['notes'] ?? '',
      'stepId' => $data['stepId'],
      'decisionSupportId' => $data['decisionSupportId'],
      'visible' => $data['visible'] ?? TRUE,
      'file' => [
        'target_id' => $file_entity->id(),
      ],
    ]);

    $entity = $decision_support_file->save();

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteDecisionSupportFile($fileId){

    $decisionSupportFile = DecisionSupportFile::load($fileId);

    if (!$decisionSupportFile) {
      throw new NotFoundHttpException();
    }
    $label = $decisionSupportFile -> getLabel();
    $newLabel = "$label - Archived";
    $decisionSupportFile->setLabel($newLabel);
    $decisionSupportFile->setVisible(false);
    $entity=$decisionSupportFile->save();

    return $entity;
  }
}