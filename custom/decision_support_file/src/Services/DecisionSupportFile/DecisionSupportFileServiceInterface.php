<?php

declare(strict_types=1);

namespace Drupal\decision_support_file\Services\DecisionSupportFile;

use Drupal\decision_support_file\Entity\DecisionSupportFile;

/**
 * Interface for decision support file service.
 */
interface DecisionSupportFileServiceInterface {

/**
 * Get a Decision Support File by ID.
 *
 * @param int $decisionSupportId
 *   The ID of the decision support file entity to retrieve.
 * @return DecisionSupportFile|null
 *   The Decision Support File entity, or NULL if not found.
 */
public function getDecisionSupportFile($decisionSupportId);

/**
 * Creates a new DecisionSupportFile entity.
 *
 * @param array $data
 *   The data for the new entity.
 *
 * @return DecisionSupportFile
 *   The created DecisionSupportFile entity.
 */
public function createDecisionSupportFile(array $data);

  /**
   * Move a existing DecisionSupportFile entity to archived.
   *
   * @param $fileId
   *   The id of the existing entity.
   */
  public function deleteDecisionSupportFile($fileId);

}