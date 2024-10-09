<?php

declare(strict_types=1);

namespace Drupal\process\Services\ProcessService;

use Drupal\process\Entity\Process;

/**
 * Interface for process service.
 */
interface ProcessServiceInterface {

  /**
   * Loads an Process entity.
   *
   * @return Process|null
   *   The Process entity, or NULL if not found.
   */
  public function getProcessList();

/**
 * Get a Process by ID.
 *
 * @param int $processId
 *   The ID of the process entity to retrieve.
 *
 * @return string
 *   The JSON representation of the Process entity.
 */
  public function getProcess($processId);

  /**
   * Creates a new Process entity.
   *
   * @param array $data
   *   The data for the new entity.
   *
   * @return Process
   *   The created Process entity.
   */
  public function createProcess(array $data);

  /**
   * Duplicates a new Process entity.
   *
   * @param array $data
   *   The data for the new entity.
   *
   * @return Process
   *   The duplicated Process entity.
   */
  public function duplicateProcess(array $data);


  /**
   * Updates a Process entity.
   *
   * @param array $data
   *   The data of the entity.
   *
   * @param $processId
   *   The id of the existing entity.
   *
   * @return Process
   *   The updated Process entity.
   */
  public function patchProcess($processId, array $data);

    /**
   * Updates a Process Json String.
   *
   * @param array $data
   *   The data of the entity.
   *
   * @param $processId
   *   The id of the existing entity.
   *
   * @return Process
   *   The updated Process entity.
   */
  public function updateProcess($processId, array $data);

  /**
   * Move a existing Process entity to archived.
   *
   * @param $processId
   *   The id of the existing entity.
   */
  public function deleteProcess($processId);



}
