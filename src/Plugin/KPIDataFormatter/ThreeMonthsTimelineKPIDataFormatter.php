<?php

/**
 * @file
 * Contains \Drupal\kpi_analytics\Plugin\KPIVisualization\RawKPIDataFormatter.php.
 */

namespace Drupal\kpi_analytics\Plugin\KPIDataFormatter;

use Drupal\kpi_analytics\Plugin\KPIDataFormatterBase;

/**
 * Provides a 'ThreeMonthsTimelineKPIDataFormatter' KPI data formatter.
 *
 * @KPIDataFormatter(
 *  id = "three_months_timeline_kpi_data_formatter",
 *  label = @Translation("3 Months Timeline KPI data formatter"),
 * )
 */
class ThreeMonthsTimelineKPIDataFormatter extends KPIDataFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function format(array $data) {
    $months = [];
    $date_formatter = \Drupal::service('date.formatter');

    for ($i = 0; $i < 3; $i++) {
      $months[] = date('Y-m', strtotime('this month - ' . $i . ' month'));
    }

    $formatted_data = [];

    if ($data) {
      foreach ($data as $value) {
        if (!in_array($value['created'], $months)) {
          continue;
        }

        $date = $value['created'];
        $time = strtotime($value['created']);
        $value['created'] = $date_formatter->format($time, '', 'F');
        $formatted_data[ $date ] = $value;
      }
    }

    foreach ($months as $month) {
      if (!isset($formatted_data[ $month ])) {
        $time = strtotime($month);
        $value = reset($data);
        $keys = array_keys($value ?: []);
        $formatted_data[ $month ] = array_fill_keys($keys, 0);
        $formatted_data[ $month ]['created'] = $date_formatter->format($time, '', 'F');
      }
    }

    ksort($formatted_data);

    return array_values($formatted_data);
  }

}
