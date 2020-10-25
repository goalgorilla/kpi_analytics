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
  public function query(BlockContentInterface $entity, $block) {
    $data = [];
    $args = [];
    if (
      $block !== NULL &&
      $block->getThirdPartySetting('kpi_analytics', 'taxonomy_filter_terms')
    ) {
      $query = $entity->field_kpi_query->value;
      preg_match_all('/:(\w+)/', $query, $placeholders);
      if (!empty($placeholders[1])) {
        foreach ($placeholders[1] as $placeholder) {
          if ($placeholder === 'ids') {
            $args[':ids[]'] = $block->getThirdPartySetting('kpi_analytics', 'taxonomy_filter_terms');
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
