<?php

namespace Drupal\kpi_analytics\Plugin\KPIDatasource;

use Drupal\kpi_analytics\Plugin\KPIDatasourceBase;

/**
 * Provides a 'DrupalKPIDatasource' KPI Datasource.
 *
 * @KPIDatasource(
 *  id = "drupal_kpi_datasource",
 *  label = @Translation("Drupal datasource"),
 * )
 */
class DrupalKPIDatasource extends KPIDatasourceBase {

  /**
   * {@inheritdoc}
   */
  public function query($query) {
    // TODO: check if we can use Views module.
    $data = [];
    $results = $this->database->query($query)->fetchAll();
    foreach ($results as $result) {
      $data[] = (array) $result;
    }
    return $data;
  }

}
