<?php

/**
 * @file
 * Contains \Drupal\kpi_analytics\Plugin\KPIVisualization\MorrisLineGraphKPIVisualization.php.
 */

namespace Drupal\kpi_analytics\Plugin\KPIVisualization;

use Drupal\kpi_analytics\Plugin\KPIVisualizationBase;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;

/**
 * Provides a 'MorrisLineGraphKPIVisualization' KPI Visualization.
 *
 * @KPIVisualization(
 *  id = "morris_line_graph_kpi_visualization",
 *  label = @Translation("Morris line graph KPI visualization"),
 * )
 */
class MorrisLineGraphKPIVisualization extends KPIVisualizationBase {

  /**
   * @inheritdoc
   * TODO: Support multiple lines in one graph.
   */
  public function render(array $data) {
    $render_array = [];

    $uuid_service = \Drupal::service('uuid');
    $uuid = $uuid_service->generate();

    $xkey = 'x';
    $ykeys = ['y'];

    if (count($data) > 0) {
      $ykeys = [];

      foreach ($data[0] as $key => $value) {
        $ykeys[] = $key;
      }

      $xkey = array_shift($ykeys);
    }

    // Data to render and Morris options.
    $options = [
      'element' => $uuid,
      'data' => $data,
      'xkey' => $xkey,
      'ykeys' => $ykeys,
      'parseTime' => FALSE,
      'labels' => ['Active users', 'Total users'],
    ];

    // Load the Morris Library.
    $render_array['kpi_analytics']['#attached']['library'][] = 'kpi_analytics/morris';
    $render_array['kpi_analytics']['#attached']['drupalSettings']['kpi_analytics']['morris']['line'][$uuid]['options'] = $options;

    $html = '<div id="' . $uuid . '" class="morris_line" style="height: 200px" data-colors="#29abe2,#ffc142"></div>';

    $render_array['kpi_analytics']['#markup'] = $html;
    return $render_array;
  }
}
