<?php

namespace Drupal\oscar_translation;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a class to build a listing of translation type entities.
 *
 * @see \Drupal\oscar_translation\Entity\NodeType
 */
class TranslationTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['title'] = t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['title'] = [
      'data' => $entity->label(),
      'class' => ['menu-label'],
    ];
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    if (isset($operations['edit'])) {
      $operations['edit']['weight'] = 30;
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('No translation types available. <a href=":link">Add translation type</a>.', [
        ':link' => Url::fromRoute('entity.translation_type.add_form')->toString(),
      ]);

    return $build;
  }

}
