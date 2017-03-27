<?php

namespace Drupal\kpi_analytics\Plugin;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for KPI Visualization plugins.
 */
abstract class KPIVisualizationBase extends PluginBase implements KPIVisualizationInterface {

  function render(array $data) {
    return 'Hello world';
  }
}
