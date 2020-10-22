<?php

namespace Drupal\kpi_analytics\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides new widget for term reverence that allows to choose vocabulary.
 *
 * @FieldWidget(
 *   id = "kpi_term",
 *   label = @Translation("KPI Term"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = TRUE
 * )
 */
class KPITermWidget extends EntityReferenceAutocompleteWidget {

  /**
   * The taxonomy term storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $taxonomyTermStorage;

  /**
   * The taxonomy vocabulary storage.
   *
   * @var \Drupal\taxonomy\VocabularyStorageInterface
   */
  protected $taxonomyVocabularyStorage;

  /**
   * KPITermWidget constructor.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

    $this->taxonomyTermStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->taxonomyVocabularyStorage = $entity_type_manager->getStorage('taxonomy_vocabulary');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\field\Entity\FieldStorageConfig $field_storage */
    $field_storage = $this->fieldDefinition->getFieldStorageDefinition();
    $form_object = $form_state->getFormObject();

    // Continue only for taxonomy term relation end content entity.
    if (
      $field_storage->getSetting('target_type') !== 'taxonomy_term' ||
      !$form_object->getEntity() instanceof ContentEntityInterface
    ) {
      return parent::formElement($items, $delta, $element, $form, $form_state);
    }

    // Variables for work.
    $field_name = $this->fieldDefinition->getName();
    $user_inputs = $form_state->getUserInput();
    $vocabulary_options = $this->getVocabularyList();
    $term_ids = [];
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $form_object->getEntity();
    $field_values = $entity->get($field_name)->getValue();
    $selected_vocabulary = '';

    // Get vocabulary of selected terms.
    if (!$entity->get($field_name)->isEmpty()) {
      $term_id = $entity->get($field_name)->target_id;
      $selected_vocabulary = $this->taxonomyTermStorage
        ->load($term_id)
        ->getVocabularyId();
    }

    if (isset($user_inputs[$field_name]['term_wrapper']['term_list'])) {
      $term_ids = $user_inputs[$field_name]['term_wrapper']['term_list'];
    }
    elseif (!empty($field_values)) {
      $term_ids = array_map(static function ($value) {
        return $value['target_id'];
      }, $field_values);
    }

    // Set default vocabulary value.
    if (
      isset($user_inputs[$field_name]['vocabulary']) &&
      $user_inputs[$field_name]['vocabulary'] !== $selected_vocabulary
    ) {
      $default_vocabulary = $user_inputs[$field_name]['vocabulary'];
    }
    else {
      $vocabulary_ids = array_keys($vocabulary_options);
      $default_vocabulary = reset($vocabulary_ids);
      if (!empty($term_ids)) {
        $default_vocabulary = $this->taxonomyTermStorage
          ->load(reset($term_ids))
          ->getVocabularyId();
      }
    }

    $element += [
      '#type' => 'container',
      '#element_validate' => [
        [$this, 'validateElement'],
      ],
      'vocabulary' => [
        '#type' => 'select',
        '#title' => $this->t('KPI Vocabulary'),
        '#options' => $vocabulary_options,
        '#value' => $default_vocabulary,
        '#default_value' => $default_vocabulary,
        '#ajax' => [
          'event' => 'change',
          'callback' => [$this, 'updateTermList'],
          'wrapper' => 'kpi-term-wrapper',
        ],
      ],
      'term_wrapper' => [
        '#type' => 'container',
        '#id' => 'kpi-term-wrapper',
        'term_list' => [
          '#type' => 'select2',
          '#title' => $this->fieldDefinition->getLabel(),
          '#options' => $this->getTermsByVocabulary($default_vocabulary),
          '#value' => $term_ids,
          '#default_value' => $term_ids,
          '#multiple' => $this->fieldDefinition->getFieldStorageDefinition()
            ->isMultiple(),
        ],
      ],
    ];

    return $element;
  }

  /**
   * Form validation handler for widget elements.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function validateElement(array $element, FormStateInterface $form_state) {
    $user_inputs = $form_state->getUserInput();
    $field_name = $element['#field_name'];
    if (isset($user_inputs[$field_name]['term_wrapper']['term_list'])) {
      $terms = $user_inputs[$field_name]['term_wrapper']['term_list'];
      $form_state->setValue($field_name, $terms);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    return $values;
  }

  /**
   * Ajax callback to update list of terms.
   *
   * @param array $form
   *   The form where the settings form is being included in.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Renderable array of term list field.
   */
  public function updateTermList(array $form, FormStateInterface $form_state) {
    return $form[$this->fieldDefinition->getName()]['widget']['term_wrapper'];
  }

  /**
   * Gets list of all vocabularies.
   *
   * @return array|null[]|string[]
   *   Vocabulary list.
   */
  protected function getVocabularyList() {
    $field_handler_settings = $this->fieldDefinition->getSetting('handler_settings');
    $allowed_vocabularies = array_keys($field_handler_settings['target_bundles']);
    $vocabularies = $this->taxonomyVocabularyStorage
      ->loadMultiple($allowed_vocabularies);
    if (!empty($vocabularies)) {
      return array_map(static function ($value) {
        return $value->label();
      }, $vocabularies);
    }

    return [];
  }

  /**
   * Gets list of terms for specific vocabulary.
   *
   * @param string $vocabulary
   *   Vocabulary ID.
   *
   * @return array|null[]|string[]
   *   Terms list.
   */
  protected function getTermsByVocabulary($vocabulary) {
    $terms = $this->taxonomyTermStorage
      ->loadByProperties([
        'vid' => $vocabulary,
        'status' => 1,
      ]);
    if (!empty($terms)) {
      return array_map(static function ($value) {
        return $value->label();
      }, $terms);
    }

    return [];
  }

}
