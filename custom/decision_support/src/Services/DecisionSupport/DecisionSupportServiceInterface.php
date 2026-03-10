<?php

declare(strict_types=1);

namespace Drupal\decision_support\Services\DecisionSupport;

/**
 * Interface for decision support service.
 */
interface DecisionSupportServiceInterface {

  /**
   * Loads a DecisionSupport entity.
   *
   * @return \Drupal\decision_support\Entity\DecisionSupport|null
   *   The DecisionSupport entity, or NULL if not found.
   */
  public function getDecisionSupportList();

  /**
   * Get a DecisionSupport by ID.
   *
   * @param int $decisionSupportId
   *   The ID of the decisionSupport entity to retrieve.
   *
   * @return string
   *   The JSON representation of the DecisionSupport entity.
   */
  public function getDecisionSupportReportList();

  /**
   * Get a DecisionSupport by ID.
   *
   * @param int $decisionSupportReportId
   *   The ID of the decisionSupport entity to retrieve.
   *
   * @return string
   *   The JSON representation of the DecisionSupport entity.
   */
  public function getDecisionSupport($decisionSupportId);

  /**
   * Creates a new DecisionSupport entity.
   *
   * @param array $data
   *   The data for the new entity.
   *
   * @return \Drupal\decision_support\Entity\DecisionSupport
   *   The created DecisionSupport entity.
   */
  public function getDecisionSupportReport($decisionSupportId);

  /**
   * Creates a new DecisionSupport entity.
   *
   * @param array $data
   *   The data for the new entity.
   *
   * @return \Drupal\decision_support\Entity\DecisionSupport
   *   The created DecisionSupport entity.
   */
  public function createDecisionSupport(array $data);

  /**
   * Updates a DecisionSupport entity.
   *
   * @param array $data
   *   The data of the entity.
   *
   * @param $decisionSupportId
   *   The id of the existing entity.
   *
   * @return \Drupal\decision_support\Entity\DecisionSupport
   *   The updated DecisionSupport entity.
   */
  public function updateDecisionSupport($decisionSupportId, array $data);

  /**
   * Move a existing DecisionSupport entity to archived.
   *
   * @param $decisionSupportId
   *   The id of the existing entity.
   */
  public function archiveDecisionSupport($decisionSupportId);

}
