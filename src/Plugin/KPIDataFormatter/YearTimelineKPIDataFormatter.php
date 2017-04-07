<?php

/**
 * @file
 * Contains \Drupal\kpi_analytics\Plugin\KPIVisualization\RawKPIDataFormatter.php.
 */

namespace Drupal\kpi_analytics\Plugin\KPIDataFormatter;

use Drupal\kpi_analytics\Plugin\KPIDataFormatterBase;

/**
 * Provides a 'YearTimelineKPIDataFormatter' KPI data formatter.
 *
 * @KPIDataFormatter(
 *  id = "year_timeline_kpi_data_formatter",
 *  label = @Translation("Year Timeline KPI data formatter"),
 * )
 */
class YearTimelineKPIDataFormatter extends KPIDataFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function format(array $data) {
    $months = [];
    $current_month = $data ? date('n') : 12;

    for ($i = 1; $i <= $current_month; $i++) {
      $months[] = date('Y-m', mktime(0, 0, 0, $i, 1));
    }

    $formatted_data = [];

    if ($data) {
      foreach ($data as $value) {
        $formatted_data[$value['created']] = $value;
      }
    }

    foreach ($months as $month) {
      if (!isset($formatted_data[ $month ])) {
        $formatted_data[ $month ] = end($data);
        $formatted_data[ $month ]['created'] = $month;
      }
    }

    ksort($formatted_data);

    return array_values($formatted_data);
  }

}
