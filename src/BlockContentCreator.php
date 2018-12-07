<?php

namespace Drupal\kpi_analytics;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class BlockContentCreator.
 *
 * @package Drupal\kpi_analytics
 */
class BlockContentCreator {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Block creator.
   *
   * @var \Drupal\kpi_analytics\BlockCreator
   */
  protected $blockCreator;

  /**
   * Block content entity.
   *
   * @var \Drupal\block_content\Entity\BlockContent
   */
  protected $entity;

  /**
   * Path to directory with the file source.
   *
   * @var string
   */
  protected $path;

  /**
   * Identifier of a block. Should be equal to filename.
   *
   * @var string
   */
  protected $id;

  /**
   * BlockContentCreator constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\kpi_analytics\BlockCreator $block_creator
   *   Block creator.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, BlockCreator $block_creator) {
    $this->entityTypeManager = $entity_type_manager;
    $this->blockCreator = $block_creator;
  }

  /**
   * Set path to directory with the file source and block ID being created.
   *
   * @param string $path
   *   Path to directory with the file source.
   * @param string $id
   *   Identifier of a block.
   */
  public function setSource($path, $id) {
    $this->path = $path;
    $this->id = $id;
  }

  /**
   * Parse data from a YAML file.
   *
   * @param bool $reset
   *   If TRUE, file will be parsed again.
   *
   * @return array
   *   Data
   */
  protected function getData($reset = FALSE) {
    if (!$this->data || $reset) {
      $source = "{$this->path}/{$this->id}.yml";
      $content = file_get_contents($source);
      $this->data = Yaml::parse($content);
    }

    return $this->data;
  }

  /**
   * Get created entity.
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Create entity with values defined in a yaml file.
   */
  public function create() {
    $data = $this->getData();
    $values = $data['values'];

    if ($block_content = $this->entityTypeManager->getStorage('block_content')->loadByProperties(['uuid' => $values['uuid']])) {
      $this->entity = current($block_content);

      return $this->entity;
    }

    // Create base instance of the entity being created.
    $this->entity = $this->entityTypeManager
      ->getStorage('block_content')
      ->create($values);

    $fields = isset($data['fields']) ? $data['fields'] : [];
    // Fill fields.
    foreach ($fields as $field_name => $value) {
      $this->entity->get($field_name)->setValue($value);
    }

    $this->entity->save();

    return $this->entity;
  }

  /**
   * Update entity with values defined in a yaml file.
   */
  public function update() {
    $data = $this->getData();
    $values = $data['values'];

    if ($block_content = $this->entityTypeManager->getStorage('block_content')->loadByProperties(['uuid' => $values['uuid']])) {
      $this->entity = current($block_content);

      $fields = isset($data['fields']) ? $data['fields'] : [];
      // Fill fields.
      foreach ($fields as $field_name => $value) {
        $this->entity->get($field_name)->setValue($value);
      }

      $this->entity->save();

      return $this->entity;
    }
  }

  /**
   * Delete block content.
   */
  public function delete() {
    $data = $this->getData();
    $values = $data['values'];

    if ($block_content = $this->entityTypeManager->getStorage('block_content')->loadByProperties(['uuid' => $values['uuid']])) {
      current($block_content)->delete();
    }
  }

  /**
   * Create instance of created block content.
   *
   * @param string $path
   *   Path to directory with the source file.
   * @param string $id
   *   Identifier of block and filename without extension.
   *
   * @return \Drupal\block\Entity\Block
   *   Block entity.
   */
  public function createBlockInstance($path, $id) {
    $block_creator = clone $this->blockCreator;
    $block_creator->setSource($path, $id);
    $block_creator->setPluginId('block_content:' . $this->entity->get('uuid')->value);

    return $block_creator->create();
  }

}
