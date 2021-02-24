<?php

namespace Drupal\kpi_analytics\Plugin\KPIVisualization;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\kpi_analytics\Plugin\KPIVisualizationBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'MorrisTagFollowGraphKPIVisualization' KPI Visualization.
 *
 * @KPIVisualization(
 *  id = "morris_tag_follow_graph_kpi_visualization",
 *  label = @Translation("Morris tag follow graph KPI visualization"),
 * )
 */
class MorrisTagFollowGraphKPIVisualization extends KPIVisualizationBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * MorrisTagFollowGraphKPIVisualization constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   *   The uuid service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, UuidInterface $uuid, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $uuid);

    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('uuid'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render(array $data) {
    $uuid = $this->uuid->generate();

    $xkey = 'label';
    $ykeys = ['current'];

    $tids = array_map(function ($value) {
      return $value['tid'];
    }, $data);

    $flagging_storage = $this->entityTypeManager->getStorage('flagging');
    $flags = $flagging_storage->getAggregateQuery()
      ->condition('entity_id', $tids, 'IN')
      ->groupBy('uid')
      ->count()
      ->execute();

    $ymax = 0;

    $diff = 0;

    foreach ($data as $value) {
      if ($value['current'] > $ymax) {
        $ymax = $value['current'];
      }

      $diff += $value['difference'];
    }

    $increase = ($ymax * 0.3) < 1 ? 1 : floor($ymax * 0.3);
    $ymax += $increase;

    // Data to render and Morris options.
    $options = [
      'element' => $uuid,
      'data' => $data,
      'xkey' => $xkey,
      'ykeys' => $ykeys,
      'parseTime' => FALSE,
      'labels' => $this->labels,
      'plugin' => 'Bar',
      'barColors' => $this->colors,
      'barSizeRatio' => '0.5',
      'horizontal' => TRUE,
      'hideHover' => TRUE,
      'ymax' => $ymax,
    ];

    return [
      '#theme' => 'kpi_analytics_morris_tag_follow_chart',
      '#type' => 'bar',
      '#uuid' => $uuid,
      '#followers' => $flags,
      '#difference' => $diff,
      '#attached' => [
        'library' => [
          'kpi_analytics/morris',
        ],
        'drupalSettings' => [
          'kpi_analytics' => [
            'morris' => [
              'chart' => [
                $uuid => [
                  'options' => $options,
                ],
              ],
            ],
          ],
        ],
      ],
    ];
  }

}
