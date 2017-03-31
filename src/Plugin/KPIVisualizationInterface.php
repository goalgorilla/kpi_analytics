<?php

namespace Drupal\kpi_analytics\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for KPI Visualization plugins.
 */
interface KPIVisualizationInterface extends PluginInspectionInterface {

  /**
   * Render the data.
   *
   * @param array $data Data to render
   * @return array render array
   */
  public function render(array $data);

  /**
   * Set a list with labels for chart.
   *
   * @param array $labels
   *   Array where each value is a label.
   */
  public function setLabels(array $labels);

}
