<?php

declare(strict_types=1);

namespace Drupal\decision_support\Services\DecisionSupport;

use Drupal\Component\Render\MarkupInterface;
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
        $decisionSupport['processLabel'] = $unformattedDecisionSupport->getProcessLabel();
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
  public function getDecisionSupportReportList() {

    $unformattedDecisionSupportReport = DecisionSupportReport::loadMultiple();
    $decisionSupportReportList = array();
    foreach ($unformattedDecisionSupportReport as $unformattedDecisionSupportReport) {
      if ($unformattedDecisionSupportReport instanceof DecisionSupportReport) {
        $decisionSupportReport['label'] = $unformattedDecisionSupportReport->getName();
        $decisionSupportReport['entityId'] = $unformattedDecisionSupportReport->id();
        $decisionSupportReport['updatedTime'] = $unformattedDecisionSupportReport->getupdatedTime();
        $decisionSupportReport['json_string'] = $unformattedDecisionSupportReport->getJsonString();

        $decisionSupportReportList[] = $decisionSupportReport;
        unset($decisionSupportReport);
      }
    }

    return $decisionSupportReportList;
  }

  /**
   * {@inheritdoc}
   */
  public function getDecisionSupportReport($decisionSupportId) {

    $decisionSupport = DecisionSupport::load($decisionSupportId);
    if (!$decisionSupport) {
      throw new NotFoundHttpException(sprintf('DecisionSupport with ID %s was not found.', $decisionSupportId));
    }
    $decisionSupportJsonString = $decisionSupport->getJsonString();

    /*my working p4 */
  $jsonData = json_decode($decisionSupportJsonString, true);

  $reportData = array();

  foreach ($jsonData['steps'] as &$step){
    $data['id'] = $step['id'];
    $data['description'] = $step['description'];
    $data['answerLabel'] = $step['answerLabel'];
    $data['textAnswer'] = strip_tags($step['textAnswer']);
    $reportData[]= $data;
  }

  $reportJson = json_encode($reportData);

  //$reportJson = strip_tags($reportJson);

  return $reportJson;

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
      'processId' =>$data['process_id'],
      'processLabel' => $process->getLabel(),
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
