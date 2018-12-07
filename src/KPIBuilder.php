<?php

namespace Drupal\kpi_analytics;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\kpi_analytics\Plugin\KPIDatasourceManager;
use Drupal\kpi_analytics\Plugin\KPIDataFormatterManager;
use Drupal\kpi_analytics\Plugin\KPIVisualizationManager;

/**
 * Class KPIBuilder.
 *
 * @package Drupal\kpi_analytics
 */
class KPIBuilder implements KPIBuilderInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Drupal\kpi_analytics\Plugin\KPIDatasourceManager definition.
   *
   * @var Drupal\kpi_analytics\Plugin\KPIDatasourceManager
   */
  protected $kpiDataSourceManager;

  /**
   * Drupal\kpi_analytics\Plugin\KPIDataFormatterManager definition.
   *
   * @var Drupal\kpi_analytics\Plugin\KPIDataFormatterManager
   */
  protected $kpiDataFormatterManager;

  /**
   * Drupal\kpi_analytics\Plugin\KPIVisualizationManager definition.
   *
   * @var Drupal\kpi_analytics\Plugin\KPIVisualizationManager
   */
  protected $kpiDataVisualizationManager;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManager $entityTypeManager, KPIDatasourceManager $kpiDataSourceManager, KPIDataFormatterManager $kpiDataFormatterManager, KPIVisualizationManager $kpiDataVisualizationManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->kpiDataSourceManager = $kpiDataSourceManager;
    $this->kpiDataFormatterManager = $kpiDataFormatterManager;
    $this->kpiDataVisualizationManager = $kpiDataVisualizationManager;
  }

  /**
   * {@inheritdoc}
   */
  public function build($entity_type_id, $entity_id) {
    /** @var \Drupal\block_content\Entity\BlockContent $entity */
    $entity = $this->entityTypeManager->getStorage($entity_type_id)->load($entity_id);
    $query = $entity->field_kpi_query->value;
    $datasource = $entity->field_kpi_datasource->value;
    $datasource_plugin = $this->kpiDataSourceManager->createInstance($datasource);
    $data = $datasource_plugin->query($query);

    $data_formatters = $entity->field_kpi_data_formatter->getValue();
    foreach ($data_formatters as $data_formatter) {
      $data_formatter_plugin = $this->kpiDataFormatterManager->createInstance($data_formatter['value']);
      $data = $data_formatter_plugin->format($data);
    }

    $visualization = $entity->field_kpi_visualization->value;
    // Retrieve the plugins.
    $visualization_plugin = $this->kpiDataVisualizationManager->createInstance($visualization);

    $labels = array_map(function ($item) {
      return $item['value'];
    }, $entity->get('field_kpi_chart_labels')->getValue());

    $colors = array_map(function ($item) {
      return $item['value'];
    }, $entity->get('field_kpi_chart_colors')->getValue());

    $render_array = $visualization_plugin
      ->setLabels($labels)
      ->setColors($colors)
      ->render($data);

    return $render_array;
  }

}
