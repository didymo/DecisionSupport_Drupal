<?php

declare(strict_types=1);

namespace Drupal\decision_support\Services\DecisionSupport;

use Drupal\decision_support\Entity\DecisionSupport;
use Drupal\process\Entity\Process;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @todo Add class description.
 */
final class DecisionSupportService implements DecisionSupportServiceInterface {

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
   * Constructs a DecisionSupportService object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function getDecisionSupportList() {

    $unformattedDecisionSupport = DecisionSupport::loadMultiple();
    $decisionSupportList = array();
    foreach ($unformattedDecisionSupport as $unformattedDecisionSupport) {
      if ($unformattedDecisionSupport instanceof DecisionSupport) {
        $decisionSupport['label'] = $unformattedDecisionSupport->getName();
        $decisionSupport['entityId'] = $unformattedDecisionSupport->id();
        $decisionSupport['revisionId'] = $unformattedDecisionSupport->getRevisionId();
        $decisionSupport['createdTime'] = $unformattedDecisionSupport->getCreatedTime();
        $decisionSupport['updatedTime'] = $unformattedDecisionSupport->getupdatedTime();
        $decisionSupport['revisionStatus'] = $unformattedDecisionSupport->getRevisionStatus();
        $decisionSupport['isCompleted'] = $unformattedDecisionSupport->getIsCompleted();
        $decisionSupport['json_string'] = $unformattedDecisionSupport->getJsonString();

        $decisionSupportList[] = $decisionSupport;
        unset($decisionSupport);
      }
    }

    return $decisionSupportList;
  }

  /**
   * {@inheritdoc}
   */
  public function getDecisionSupport($decisionSupportId) {

    $decisionSupport = DecisionSupport::load($decisionSupportId);
    if (!$decisionSupport) {
      throw new NotFoundHttpException(sprintf('DecisionSupport with ID %s was not found.', $decisionSupportId));
    }
    $decisionSupportJsonString = $decisionSupport->getJsonString();

    return $decisionSupportJsonString;
  }

  /**
   * {@inheritdoc}
   */
  public function createDecisionSupport(array $data) {

    $processId = $data['process_id'];
    $process = Process::load($processId);
    $processJson = $process->getJsonString();
    $processData = json_decode($processJson, true);

    $decisionSupport = DecisionSupport::create($data);
    $entityId = $decisionSupport->save();
    $returnValue['entityId'] = $decisionSupport->id();
    $jsonstring = [
      'entityId' =>$decisionSupport->id(),
      'uuid'=>uniqid(),
      'decisionSupportLabel' =>$decisionSupport->label(),
      'processId' =>$data['decisionSupport_id'],
      'processLabel' => $decisionSupport->getName(),
      'steps'=> $processData['steps'],
      'isCompleted' =>  $decisionSupport->getIsCompleted() ,
    ];
    $decisionSupportJsonstring = json_encode($jsonstring);
    $decisionSupport->setJsonString($decisionSupportJsonstring);
    $entity=$decisionSupport->save();

    // log the creation of the entity.
    $this->logger->notice('Created new DecisionSupport entity with ID @id.', ['@id' => $returnValue]);
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function updateDecisionSupport($decisionSupportId, array $data)
  {
    $decisionSupport = DecisionSupport::load($decisionSupportId);

    if (!$decisionSupport) {
      throw new NotFoundHttpException(sprintf('DecisionSupport with ID %s was not found.', $decisionSupportId));
    }
    $json_string = json_encode($data);
    $decisionSupport->setJsonString($json_string);
    $entity=$decisionSupport->save();
    
    return $entity;
  }


  /**
   * {@inheritdoc}
   */
  public function archiveDecisionSupport($decisionSupportId){

    $decisionSupport = DecisionSupport::load($decisionSupportId);
    if (!$decisionSupport) {
      throw new NotFoundHttpException(sprintf('DecisionSupport with ID %s was not found.', $decisionSupportId));
    }
    
    $decisionSupport->delete();

    $this->logger->notice('Moved DecisionSupport with ID @id to archived.', ['@id' => $decisionSupportId]);

  }

}
