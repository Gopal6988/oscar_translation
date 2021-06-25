<?php

namespace Drupal\oscar_translation\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\NumericFilter;
use Drupal\Core\Database\Query\Condition;

/**
 * Filters by phase or status of project.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("translation_used_count")
 */
class TranslationUsedCount extends NumericFilter {

  /**
   * The current display.
   *
   * @var string
   *   The current display of the view.
   */
  protected $currentDisplay;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    return $options;
  }

  /**
   * Helper function that builds the query.
   */
  public function query() {
    if (!empty($this->value)) {
      $fieldName = 'reference_data.nids_count';
      $min = $this->value['min'];
      $max = $this->value['max'];

      if (is_numeric($min) && is_numeric($max)) {
        if ($min == 0) {
          $conditionGroup = (new Condition('OR'))->condition($fieldName, $this->value, 'BETWEEN')->isNull($fieldName);

          $this->query->addWhere('AND', $conditionGroup);
        }
        else {
          $this->query->addWhere('AND', $fieldName, $this->value, 'BETWEEN');
        }
      }
      elseif (is_numeric($min)) {
        if ($min == 0) {
          $conditionGroup = (new Condition('OR'))->condition($fieldName, $min, '>=')->isNull($fieldName);

          $this->query->addWhere('AND', $conditionGroup);
        }
        else {
          $this->query->addWhere('AND', $fieldName, $min, '>=');
        }
      }
      else {
        $conditionGroup = (new Condition('OR'))->condition($fieldName, $max, '<=')->isNull($fieldName);
        $this->query->addWhere('AND', $conditionGroup);
      }
    }
  }

}
