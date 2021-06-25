<?php

namespace Drupal\oscar_translation\Plugin\views\field;

use Drupal\views\Plugin\views\field\NumericField;
use Drupal\views\ResultRow;
use Drupal\views\Views;

/**
 * Field handler to present the path to the node.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("translation_used_count")
 *  Use @ViewsField("entity_link") with 'output_url_as_text' set.
 */
class TranslationUsedCount extends NumericField {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $subQuery = \Drupal::database()->select('oscar_translations', 't');
    $subQuery->addField('t', 'id');
    $subQuery->addExpression('(length(t.nids) - length(replace(t.nids, :comma, :empty)) - 1)', 'nids_count', [':comma' => ',', ':empty' => '']);
    $joinDefinition = [
      'table formula' => $subQuery,
      'field' => 'id',
      'left_table' => 'oscar_translations',
      'left_field' => 'id',
      'operator' => '=',
    ];
    $join = Views::pluginManager('join')->createInstance('standard', $joinDefinition);
    $this->query->addRelationship('reference_data', $join, 'oscar_translations');

    $this->field_alias = $this->query->addField(NULL, 'nids_count', 'nids_count');
    $this->addAdditionalFields(['nids_count']);
  }

  /**
   * {@inheritdoc}
   */
  public function clickSort($order) {
    if (isset($this->field_alias)) {
      $params = $this->options['group_type'] != 'group' ? ['function' => $this->options['group_type']] : [];
      $this->query->addOrderBy(NULL, 'nids_count', $order, $this->field_alias, $params);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $nids_count = $this->getValue($values, 'nids_count');
    if (is_null($nids_count)) {
      $values->nids_count = 0;
    }
    return parent::render($values);
  }

}
