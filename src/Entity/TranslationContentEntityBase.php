<?php

namespace Drupal\oscar_translation\Entity;

use Drupal\Core\Entity\ContentEntityBase;

class TranslationContentEntityBase extends ContentEntityBase {
  public $translationService;

  public function __construct(array $values, $entity_type, $bundle = FALSE, $translations = []) {
    parent::__construct($values, $entity_type, $bundle, $translations);
    $this->translationService = \Drupal::service('oscar_translation.translation');
  }
}