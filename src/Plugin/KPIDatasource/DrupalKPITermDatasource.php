<?php

namespace Drupal\kpi_analytics\Plugin\KPIDatasource;

use Drupal\block_content\BlockContentInterface;
use Drupal\kpi_analytics\Plugin\KPIDatasourceBase;

/**
 * Provides a 'DrupalKPIDatasource' KPI Datasource.
 *
 * @KPIDatasource(
 *  id = "drupal_kpi_term_datasource",
 *  label = @Translation("Drupal term datasource"),
 * )
 */
class DrupalKPITermDatasource extends KPIDatasourceBase {

  /**
   * {@inheritdoc}
   */
  public function query(BlockContentInterface $entity) {
    $data = [];
    $args = [];
    if (!$entity->get('field_kpi_term')->isEmpty()) {
      $field_values = $entity->get('field_kpi_term')->getValue();
      $query = $entity->field_kpi_query->value;
      preg_match_all('/:(\w+)/', $query, $placeholders);
      if (!empty($placeholders[1])) {
        foreach ($placeholders[1] as $placeholder) {
          if ($placeholder === 'ids') {
            $args[':ids[]'] = array_map(function ($value) {
              return $value['target_id'];
            }, $field_values);
          }
        }
      }
      $results = $this->database->query($query, $args)->fetchAll();
      foreach ($results as $result) {
        $data[] = (array) $result;
      }
    }

    return $data;
  }

}
