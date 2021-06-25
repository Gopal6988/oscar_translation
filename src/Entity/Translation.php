<?php

namespace Drupal\oscar_translation\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\oscar_translation\TranslationInterface;

/**
 * Defines the translation entity.
 *
 * @ContentEntityType(
 *   id = "oscar_translation",
 *   label = @Translation("Translation"),
 *   base_table = "oscar_translations",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "bundle" = "bundle",
 *     "label" = "title",
 *   },
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\oscar_translation\TranslationListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\oscar_translation\TranslationAccessControlHandler",
 *      "form" = {
 *       "default" = "Drupal\oscar_translation\Form\TranslationEntityForm",
 *       "add" = "Drupal\oscar_translation\Form\TranslationEntityForm",
 *       "edit" = "Drupal\oscar_translation\Form\TranslationEntityForm",
 *       "delete" = "Drupal\oscar_translation\Form\TranslationEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   links = {
 *     "canonical" = "/translation/{oscar_translation}",
 *     "add-page" = "/translation/add",
 *     "add-form" = "/translation/add/{translation_type}/{format}",
 *     "edit-form" = "/translation/{oscar_translation}/edit",
 *     "delete-form" = "/translation/{oscar_translation}/delete",
 *     "collection" = "/translation/list"
 *   },
 *   admin_permission = "administer translation entity",
 *   bundle_entity_type = "translation_type",
 *   field_ui_base_route = "entity.translation_type.edit_form",
 * )
 */
class Translation extends TranslationContentEntityBase implements TranslationInterface {
  /**
   * {@inheritdoc}
   *
   * Define the field properties here.
   *
   * Field name, type and size determine the table structure.
   *
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['source_text'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Source Text'))
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'rows' => 2,
        'weight' => 0,
        'format' => 'plain_text',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['source_text_plain'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Source Text Plain'))
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'rows' => 2,
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['source_text_space'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Source Text Space'))
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'rows' => 2,
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['target_text'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Target Text'))
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'rows' => 2,
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['target_text_plain'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Target Text Plain'))
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'rows' => 2,
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['target_text_space'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Target Text Space'))
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'rows' => 2,
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['nids'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Count'))
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'rows' => 2,
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    return $fields;
  }
}