<?php

namespace Drupal\kpi_analytics\Plugin\KPIDataFormatter;

use Drupal\kpi_analytics\Plugin\KPIDataFormatterBase;
use Drupal\taxonomy\TermInterface;

/**
 * Provides a 'TagsFollowKPIDataFormatter' KPI data formatter.
 *
 * @KPIDataFormatter(
 *  id = "tags_follow_kpi_data_formatter",
 *  label = @Translation("Tags follow KPI data formatter"),
 * )
 */
class TagsFollowKPIDataFormatter extends KPIDataFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function format(array $data) {
    $formatted_data = [];

    // Combine multiple values in one value.
    foreach ($data as $value) {
      if ($value['current'] == 0 && $value['difference'] == '0') {
        continue;
      }

      $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');

      /** @var \Drupal\taxonomy\TermInterface $term */
      $term = $term_storage->load($value['tid']);
      if ($term instanceof TermInterface) {
        $value['label'] = $term->label();
      }
      $formatted_data[] = $value;
    }
    return $formatted_data;
  }

}
