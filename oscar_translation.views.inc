<?php

/**
 * @file
 * Views alteration file for Instruction.
 */

/**
 * Implements hook_views_data_alter().
 */
function oscar_translation_views_data_alter(array &$data) {
  $data['oscar_translations']['translation_used_count'] = [
    'title' => t('Translation Count'),
    'field' => [
      'title' => t('No. Times Used'),
      'help' => t('Provide number of times translation count for the List dashboard.'),
      'id' => 'translation_used_count',
      'click sortable' => TRUE,
    ],
    'filter' => [
      'title' => t('No. Times Used'),
      'help' => t('Provide number of times translation count filter for the Instruction List dashboard.'),
      'field' => 'nids',
      'id' => 'translation_used_count',
      'allow empty' => 1,
    ],
  ];
}
