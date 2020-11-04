<?php

namespace Drupal\kpi_analytics\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for KPI Data Formatter plugins.
 */
interface KPIDataFormatterInterface extends PluginInspectionInterface {

  /**
   * Format the data.
   *
   * @param array $data
   *   Input array.
   *
   * @return array
   *   Output array.
   */
  public function format(array $data);

}
