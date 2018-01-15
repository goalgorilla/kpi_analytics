<?php

namespace Drupal\kpi_analytics;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\Yaml\Yaml;

class BlockContentCreator {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\kpi_analytics\BlockCreator
   */
  protected $blockCreator;

  /**
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
   * Identifier of a block.
   * Should be equal to filename.
   *
   * @var string
   */
  protected $id;

  /**
   * BlockContentCreator constructor.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\kpi_analytics\BlockCreator $block_creator
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, BlockCreator $block_creator) {
    $this->entityTypeManager = $entity_type_manager;
    $this->blockCreator = $block_creator;
  }

  /**
   * Set path to directory with the file source and
   * identifier of the block being created.
   *
   * @param string $path
   * @param string $id
   */
  public function setSource($path, $id) {
    $this->path = $path;
    $this->id = $id;
  }

  /**
   * Parse data from a yaml file.
   *
   * @return array
   */
  protected function getData() {
    $source = "{$this->path}/{$this->id}.yml";
    $content = file_get_contents($source);
    $data = Yaml::parse($content);

    return $data;
  }

  /**
   * Get created entity.
   * In case when entity with provided identifier already
   * exists, this method will return existing entity.
   *
   * @return \Drupal\block_content\Entity\BlockContent|null
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Create entity with values defined in a yaml file.
   *
   * @return \Drupal\block_content\Entity\BlockContent
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
   *
   * @return \Drupal\block_content\Entity\BlockContent
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
   * @param $path
   *   Path to directory with the source file.
   * @param $id
   *   Identifier of block and filename without extension.
   *
   * @return \Drupal\block\Entity\Block
   */
  public function createBlockInstance($path, $id) {
    $block_creator = clone $this->blockCreator;
    $block_creator->setSource($path, $id);
    $block_creator->setPluginId('block_content:' . $this->entity->get('uuid')->value);

    return $block_creator->create();
  }

}
