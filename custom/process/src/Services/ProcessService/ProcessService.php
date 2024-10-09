<?php

declare(strict_types=1);

namespace Drupal\process\Services\ProcessService;


use Drupal\process\Entity\Process;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Service Class for handling Process.
 */
final class ProcessService implements ProcessServiceInterface {

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
  public function getProcessList() {

    $unformattedProcesses = Process::loadMultiple();
    $processList = array();
    foreach ($unformattedProcesses as $unformattedProcess) {
      if ($unformattedProcess instanceof Process) {
        $process['label'] = $unformattedProcess->getLabel();
        $process['entityId'] = $unformattedProcess->id();
        $process['revisionId'] = $unformattedProcess->getRevisionId();
        $process['revisionCreationTime'] = $unformattedProcess->getRevisionCreationTime();
        $process['createdTime'] = $unformattedProcess->getCreatedTime();
        $process['updatedTime'] = $unformattedProcess->getupdatedTime();
        $process['revisionStatus'] = $unformattedProcess->getRevisionStatus();
        $process['enabled'] = $unformattedProcess->getStatus();
        $process['json_string'] = $unformattedProcess->getJsonString();

        $processList[] = $process;
        unset($process);
      }
    }

    return $processList;
  }

  /**
   * {@inheritdoc}
   */
  public function getProcess($processId) {

    $process = Process::load($processId);

    if(!$process){
      throw new NotFoundHttpException(sprintf('Process with ID %s was not found.', $processId));
    }
    $processJsonString = $process->getJsonString();

    return $processJsonString;
  }

  /**
   * {@inheritdoc}
   */
  public function createProcess(array $data) {

    $process = Process::create($data);

    $entityId = $process->save();
    $returnValue['entityId'] = $process->id();
    $jsonstring = [
      'entityId' =>$process->id(),
      'uuid'=>uniqid(),
      'label' =>$process->label(),
      'steps'=>[]
    ];
    $processJsonstring = json_encode($jsonstring);
    $process->setJsonString($processJsonstring);
    $process->setRevisionStatus($data['revision_status']);
    $entity=$process->save();

    // log the creation of the entity.
    $this->logger->notice('Created new Process entity with ID @id.', ['@id' => $returnValue]);
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function duplicateProcess(array $data) {

    $process = Process::create($data);

    $entityId = $process->save();
    $returnValue['entityId'] = $process->id();
    $data_jsonstring = json_decode($data['json_string'],true);
    $newjsonstring = [
      'entityId' =>$process->id(),
      'uuid'=>uniqid(),
      'label' =>$process->label(),
      'steps'=>$data_jsonstring['steps']
    ];
    $processJsonstring = json_encode($newjsonstring);
    $process->setJsonString($processJsonstring);
    $process->setRevisionStatus($data['revision_status']);
    $entity=$process->save();

    // log the creation of the entity.
    $this->logger->notice('Duplicated Process entity with new ID @id.', ['@id' => $returnValue]);
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function patchProcess($processId, array $data)
  {
    $process = Process::load($processId);

    if (!$process) {
      throw new NotFoundHttpException(sprintf('Process with ID %s was not found.', $processId));
    }

    $process->setLabel($data['label']);
    $process->setRevisionStatus($data['revision_status']);
    $entity=$process->save();

    $this->logger->notice('The Process @id has been updated.', ['@id' => $processId]);

    return $entity;
  }

    /**
   * {@inheritdoc}
   */
  public function updateProcess($processId, array $data)
  {
    $process = Process::load($processId);

    if (!$process) {
      throw new NotFoundHttpException(sprintf('Process with ID %s was not found.', $processId));
    }
    $json_string = json_encode($data);

    $process->setJsonString($json_string);
    $entity=$process->save();

    $this->logger->notice('The Process @id has been updated.', ['@id' => $processId]);

    return $entity;
  }


  /**
   * {@inheritdoc}
   */
  public function deleteProcess($processId){

    $process = Process::load($processId);
    if (!$process) {
      throw new NotFoundHttpException(sprintf('Process with ID %s was not found.', $processId));
    }
    $label = $process -> getLabel();
    $newLabel = "$label - Archived";
    $process->setLabel($newLabel);
    $process->setStatus(false);
    $process->setRevisionStatus("Archived");
    $entity=$process->save();

    $this->logger->notice('Moved Process with ID @id to archived.', ['@id' => $processId]);

    return $entity;
  }


}
