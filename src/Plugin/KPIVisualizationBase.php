<?php

namespace Drupal\kpi_analytics\Plugin;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for KPI Visualization plugins.
 */
abstract class KPIVisualizationBase extends PluginBase implements KPIVisualizationInterface {

  /**
   * Contains a list with labels for chart.
   *
   * @var array
   */
  protected $labels = [];

  /**
   * {@inheritdoc}
   */
  public function render(array $data) {
    return 'Hello world';
  }

  /**
   * {@inheritdoc}
   */
  public function setLabels(array $labels) {
    $this->labels = $labels;
  }

}
