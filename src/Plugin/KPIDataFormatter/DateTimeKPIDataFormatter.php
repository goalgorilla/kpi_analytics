<?php

namespace Drupal\kpi_analytics\Plugin\KPIDataFormatter;

use Drupal\kpi_analytics\Plugin\KPIDataFormatterBase;

/**
 * Provides a 'DateTimeKPIDataFormatter' KPI data formatter.
 *
 * @KPIDataFormatter(
 *  id = "datetime_kpi_data_formatter",
 *  label = @Translation("Datetime KPI data formatter"),
 * )
 */
class DateTimeKPIDataFormatter extends KPIDataFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function format(array $data) {
    $formatted_data = [];

    usort($data, $this->sortByField('created'));

    // Combine multiple values in one value.
    foreach ($data as $value) {
      if (isset($value['created'])) {
        $value['created'] = $this->dateFormatter->format($value['created'], '', 'Y-m');
      }
      $formatted_data[] = $value;
    }
    return $formatted_data;
  }

  /**
   * Simple sort callback.
   *
   * @param string $field
   *   Field name.
   *
   * @return \Closure
   *   Sorting callback.
   */
  public function sortByField($field) {
    return function ($a, $b) use ($field) {
      if ($a[$field] == $b[$field]) {
        return 0;
      }
      else {
        return ($a[$field] < $b[$field]) ? -1 : 1;
      }
    };
  }

}