<?php

namespace Drupal\oscar_translation;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for oscar_translation_contact entity.
 *
 * @ingroup oscar_translation
 */
class TranslationListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   *
   * We override ::render() so that we can add our own content above the table.
   * parent::render() is where EntityListBuilder creates the table using our
   * buildHeader() and buildRow() implementations.
   */
  public function render() {
    $build = parent::render();
    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * Building the header and content lines for the contact list.
   *
   * Calling the parent::buildHeader() adds a column for the possible actions
   * and inserts the 'edit' and 'delete' links as defined for the entity type.
   */
  public function buildHeader() {
    $header['id'] = $this->t('Translation Id');
    $header['source_text'] = $this->t('Source Text');
    $header['target_text'] = $this->t('Target Text');
    $header['nids'] = $this->t('Count');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['id'] = $entity->id();
    $row['source_text'] = $entity->target_text->value;
    $row['target_text'] = $entity->target_text->value;
    $row['nids'] = 0;
    $row['nids'] = $entity->nids->value;
    return $row + parent::buildRow($entity);
  }
}