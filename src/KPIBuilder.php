<?php

namespace Drupal\kpi_analytics;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\kpi_analytics\Plugin\KPIDataFormatterManager;
use Drupal\kpi_analytics\Plugin\KPIDatasourceManager;
use Drupal\kpi_analytics\Plugin\KPIVisualizationManager;

/**
 * Class KPIBuilder.
 *
 * @package Drupal\kpi_analytics
 */
class KPIBuilder implements KPIBuilderInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The kpi datasource manager.
   *
   * @var \Drupal\kpi_analytics\Plugin\KPIDatasourceManager
   */
  protected $kpiDatasourceManager;

  /**
   * The kpi dataformatter manager.
   *
   * @var \Drupal\kpi_analytics\Plugin\KPIDataFormatterManager
   */
  protected $kpiDataFormatterManager;

  /**
   * The kpi visualization manager.
   *
   * @var \Drupal\kpi_analytics\Plugin\KPIVisualizationManager
   */
  protected $kpiVisualizationManager;

  /**
   * KPIBuilder constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\kpi_analytics\Plugin\KPIDatasourceManager $kpi_datasource_manager
   *   The kpi datasource manager.
   * @param \Drupal\kpi_analytics\Plugin\KPIDataFormatterManager $kpi_data_formatter_manager
   *   The kpi dataformatter manager.
   * @param \Drupal\kpi_analytics\Plugin\KPIVisualizationManager $kpi_visualization_manager
   *   The kpi visualization manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    KPIDatasourceManager $kpi_datasource_manager,
    KPIDataFormatterManager $kpi_data_formatter_manager,
    KPIVisualizationManager $kpi_visualization_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->kpiDatasourceManager = $kpi_datasource_manager;
    $this->kpiDataFormatterManager = $kpi_data_formatter_manager;
    $this->kpiVisualizationManager = $kpi_visualization_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function build($entity_type_id, $entity_id) {
    /** @var \Drupal\block_content\Entity\BlockContent $entity */
    $entity = $this->entityTypeManager->getStorage($entity_type_id)
      ->load($entity_id);
    $datasource = $entity->field_kpi_datasource->value;
    $datasource_plugin = $this->kpiDatasourceManager
      ->createInstance($datasource);
    $data = $datasource_plugin->query($entity);

    $data_formatters = $entity->field_kpi_data_formatter->getValue();
    foreach ($data_formatters as $data_formatter) {
      $data_formatter_plugin = $this->kpiDataFormatterManager
        ->createInstance($data_formatter['value']);
      $data = $data_formatter_plugin->format($data);
    }

    $visualization = $entity->field_kpi_visualization->value;
    // Retrieve the plugins.
    $visualization_plugin = $this->kpiVisualizationManager
      ->createInstance($visualization);

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
