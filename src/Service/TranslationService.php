<?php

namespace Drupal\oscar_translation\Service;

use Drupal\Core\Database\Connection;
use \Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\oscar_translation\Entity\Translation;
use Drupal\taxonomy\Entity\Term;

class TranslationService  {
  public $tableName = 'oscar_translations';
  public $slTableName = 'oscar_translation__field_source_language';
  public $tlTableName = 'oscar_translation__field_target_language';
  protected $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Get Translation entity storage.
   */
  public function getTranslationStorage() {
    $entityTypeManager = \Drupal::service('entity_type.manager');
    return $entityTypeManager->getStorage('oscar_translation');
  }

  /**
   * Get Translation entity query.
   */
  public function getTranslationStorageQuery() {
    return $this->getTranslationStorage()->getQuery();
  }

  /**
   * Get All the translations based on the id.
   */
  public function getAllTranslationSuggestionByIds($ids = []) {
    $ids = explode(',', $ids);
    $entityIds = $this->getTranslationStorageQuery()
      ->condition('id', $ids, 'IN')
      ->sort('id', 'desc')
      ->pager(8)
      ->execute();
  
    $data = Translation::loadMultiple($entityIds);
    return $data;
  }

  public function checkTranslationExists($values, $id) {
    $sourceText = $values['source_text'][0]['value'];
    $sourceLanguage = $values['field_source_language'][0]['target_id'];
    $targetText = $values['target_text'][0]['value'];
    $targetLanguage = $values['field_target_language'][0]['target_id'];

    $sourceTextPlain = $this->convertTranslationText($sourceText);
    $targetTextPlain = $this->convertTranslationText($targetText);

    $query = $this->getTranslationStorageQuery();
    $query = $query->condition('source_text_plain', $sourceTextPlain)
      ->condition('target_text_plain', $targetTextPlain)
      ->condition('field_source_language', $sourceLanguage)
      ->condition('field_target_language', $targetLanguage);
      if(!empty($id)) {
        $query->condition('id', $id, '<>');
      }
    $count = $query->count()->execute();
    if($count > 0) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Strip tags and convert &nbsp; to space.
  */
  public function convertTextForSpace($text) {
    $text = strip_tags($text);

    // Start To remove the name space in both the textarea and format_text fields
    $text = htmlentities($text, null, 'utf-8');
    $text = str_replace('&nbsp;', ' ', $text);
    $text = html_entity_decode($text);
    $text = str_replace('&nbsp;', ' ', $text);
    // End To remove the name space in both the textarea and format_text fields

    $text = trim($text);
    return $text;
  }

  /**
  * Convert text for the Title field.
  */
  public function convertTextForTitle($text) {
    $text = $this->convertTextForSpace($text);

    if(strlen($text) > 100) {
      $text = substr($text, 0, 100) . '...';
    }
    return $text;
  }

  /**
  * Convert translation text to plain text to store in the database.
  */
  public function convertTranslationText($text) {
    $text = strip_tags($text);
    // Start To remove the name space in both the textarea and format_text fields
    $text = htmlentities($text, null, 'utf-8');
    $text = str_replace('&nbsp;', '', $text);
    $text = html_entity_decode($text);
    $text = str_replace('&nbsp;', '', $text);
    // End To remove the name space in both the textarea and format_text fields

    $text = preg_replace('/[\t\n\r\s]+/', '', $text);
    $text = trim($text);
    return $text;
  }

  /**
  * Insert new record in translation.
  */
  public function insertNewTranslationRecord($allFields) {
    $title = $this->convertTextForTitle($allFields['source_text']['value']);
    $allFields['bundle'] = 'translation';
    $allFields['title'] = $title;
    Translation::create($allFields)->save();
  }

  /**
  * Remove nid from existing records.
  */
  public function removeNidFromTranslationRecord($data) {
    $insertNid = $data['insertNid'];
    $updateNid = $data['updateNid'];
    $sourceTextPlain = $data['sourceTextPlain'];
    $sourceLanguage = $data['sourceLanguage'];
    $targetLanguage = $data['targetLanguage'];

    $matchNid = '%' . $this->database->escapeLike($insertNid) . '%';
    $query = $this->database->select($this->tableName, 't');
    $query->join($this->slTableName, 'sl', 't.id = sl.entity_id');
    $query->join($this->tlTableName, 'tl', 't.id = tl.entity_id');
    $query->addField('t', 'id');

    $id = $query->condition('source_text_plain', $sourceTextPlain)
      ->condition('sl.field_source_language_target_id', $sourceLanguage)
      ->condition('tl.field_target_language_target_id', $targetLanguage)
      ->condition('nids', $matchNid, 'LIKE')
      ->execute()->fetchField();

    if(!empty($id)) {
      $translationLoad = Translation::load($id);
      $nids = $translationLoad->get('nids')->value;
      $nids = str_replace($updateNid, '', $nids);
      $translationLoad->set('nids', $nids)->save();
    }
  }

  /**
  * Handle Matched translation results.
  */
  public function handleMatchedTranslationRecord($data, $format) {
    $source = $data['sourceText'];
    $sourceTextSpace = $data['sourceTextSpace'];
    $sourceTextPlain = $data['sourceTextPlain'];
    $sourceLanguage = $data['sourceLanguage'];
    $target = $data['targetText'];
    $targetTextSpace = $data['targetTextSpace'];
    $targetTextPlain = $data['targetTextPlain'];
    $targetLanguage = $data['targetLanguage'];
    $insertNid = $data['insertNid'];
    $updateNid = $data['updateNid'];

    $allFields = [
      'source_text' => ['value' => $source, 'format' => $format],
      'source_text_space' => $sourceTextSpace,
      'source_text_plain' => $sourceTextPlain,
      'field_source_language' => $sourceLanguage,
      'target_text' => ['value' => $target, 'format' => $format],
      'target_text_space' => $targetTextSpace,
      'target_text_plain' => $targetTextPlain,
      'field_target_language' => $targetLanguage,
      'nids' => $insertNid,
    ];

    $query = $this->database->select($this->tableName, 't');
    $query->join($this->slTableName, 'sl', 't.id = sl.entity_id');
    $query->join($this->tlTableName, 'tl', 't.id = tl.entity_id');
    $query->fields('t', ['id', 'target_text_plain', 'nids'])
      ->condition('source_text_plain', $sourceTextPlain)
      ->condition('sl.field_source_language_target_id', $sourceLanguage)
      ->condition('tl.field_target_language_target_id', $targetLanguage)
      ->orderBy('id', 'desc');
    $getAllSourceResultCount = $query->countQuery()->execute()->fetchField();

    // Get all the source result to check the match percentage.
    if($getAllSourceResultCount > 0) {
      $matchNotExists = TRUE;

      $getAllSourceResult = $query->execute()->fetchAll();
      foreach ($getAllSourceResult as $value) {
        $result = (array) $value;

        if(!empty($result['id'])) {
          similar_text($targetTextPlain, $result['target_text_plain'], $percentage);
          $stringDiffCount = abs(strlen($targetTextPlain) - strlen($result['target_text_plain']));

          // If matching is more than 95% and the length differnce is less than 4. Update target text to the existing translation .
          if(($percentage >= 95) && ($stringDiffCount < 4)) {
            $nids = $result['nids'] . $updateNid;

            $this->removeNidFromTranslationRecord($data);
            $translationLoad = Translation::load($result['id']);
            $translationLoad->set('target_text', ['value' => $target, 'format' => $format])
              ->set('target_text_space', $targetTextSpace)
              ->set('target_text_plain', $targetTextPlain)
              ->set('nids', $nids)
              ->save();

            $matchNotExists = FALSE;
            break;
          }
        }
      }

      // When no matches, insert a new record.
      if($matchNotExists) {
        $this->removeNidFromTranslationRecord($data);
        $this->insertNewTranslationRecord($allFields);
      }
    }
    // When no matches, insert a new record.
    else {
      $this->insertNewTranslationRecord($allFields);
    }
  }

  /**
   * Get Translation entity query.
   */
  public function getAllTranslationIdsBySource($source, $tableData) {
    $data = [
      'entityIds' => [],
      'source' => 'true',
    ];

    $ids = $this->getSourceLanguageBasedTranslationQuery($source, $tableData);
    if(count($ids) > 0) {
      $data['entityIds'] = array_keys($ids);
    }
    else{
      $ids = $this->getTargetLanguageBasedTranslationQuery($source, $tableData);

      if(count($ids) > 0) {
        $data['entityIds'] = array_keys($ids);
        $data['source'] = 'false';
      }
    }

    return $data;
  }

  public function getSourceLanguageBasedTranslationQuery($source, $tableData) {
    $sourceTextPlain = $this->convertTranslationText($source);
    $sourceLanguage = $tableData['source_language'];
    $targetLanguage = $tableData['target_language'];

    $query = $this->database->select($this->tableName, 't');
    $query->join($this->slTableName, 'sl', 't.id = sl.entity_id');
    $query->join($this->tlTableName, 'tl', 't.id = tl.entity_id');
    $query->addField('t', 'id', 'id');
    $query->addField('t', 'target_text__value', 'text');
    $query->addExpression('(LENGTH(t.nids) - LENGTH(REPLACE(t.nids, \',\', \'\')))', 'count');

    $ids = $query->condition('t.source_text_plain', $sourceTextPlain)
      ->condition('sl.field_source_language_target_id', $sourceLanguage)
      ->condition('tl.field_target_language_target_id', $targetLanguage)
      ->orderBy('count', 'desc')->orderBy('t.id', 'desc')
      ->execute()->fetchAllAssoc('id');

    return $ids;
  }

  public function getTargetLanguageBasedTranslationQuery($source, $tableData) {
    $sourceTextPlain = $this->convertTranslationText($source);
    $sourceLanguage = $tableData['source_language'];
    $targetLanguage = $tableData['target_language'];

    $sourceLanguageData = Term::load($sourceLanguage);
    $sourceLanguageCode = $sourceLanguageData->getName();

    $targetLanguageData = Term::load($targetLanguage);
    $targetLanguageCode = $targetLanguageData->get('field_language_code')->value;

    $query = $this->database->select($this->tableName, 't');
    $query->join($this->slTableName, 'sl', 't.id = sl.entity_id');
    $query->join($this->tlTableName, 'tl', 't.id = tl.entity_id');
    $query->join('taxonomy_term__field_language_code', 'tlc', 'tl.field_target_language_target_id = tlc.entity_id');
    $query->join('taxonomy_term_field_data', 'tfd', 'sl.field_source_language_target_id = tfd.tid');
    $query->addField('t', 'id', 'id');
    $query->addField('t', 'source_text__value', 'text');
    $query->addExpression('(LENGTH(t.nids) - LENGTH(REPLACE(t.nids, \',\', \'\')))', 'count');

    $ids = $query->condition('t.target_text_plain', $sourceTextPlain)
    ->condition('tlc.field_language_code_value', $sourceLanguageCode)
    ->condition('tfd.name ', $targetLanguageCode)
    ->orderBy('count', 'desc')->orderBy('t.id', 'desc')
    ->execute()->fetchAllAssoc('id');

    return $ids;
  }

  /**
  * To Get Translation data from the database.
  */
  public function getDefaultTranslationData($source, $tableData) {
    $text = '';
    $sourceTextPlain = $this->convertTranslationText($source);
    $sourceLanguage = $tableData['source_language'];
    $targetLanguage = $tableData['target_language'];

    $query = $this->database->select($this->tableName, 't');
    $query->join($this->slTableName, 'sl', 't.id = sl.entity_id');
    $query->join($this->tlTableName, 'tl', 't.id = tl.entity_id');
    $query->addField('t', 'target_text__value', 'text');
    $query->addExpression('(LENGTH(t.nids) - LENGTH(REPLACE(t.nids, \',\', \'\')))', 'count');

    $text = $query->condition('t.source_text_plain', $sourceTextPlain)
    ->condition('sl.field_source_language_target_id', $sourceLanguage)
    ->condition('tl.field_target_language_target_id', $targetLanguage)
    ->orderBy('count', 'desc')->orderBy('t.id', 'desc')
    ->range(0, 1)->execute()->fetchField();

    if(empty($text)) {
      $sourceLanguageData = Term::load($sourceLanguage);
      $sourceLanguageCode = $sourceLanguageData->getName();

      $targetLanguageData = Term::load($targetLanguage);
      $targetLanguageCode = $targetLanguageData->get('field_language_code')->value;

      $query = $this->database->select($this->tableName, 't');
      $query->join($this->slTableName, 'sl', 't.id = sl.entity_id');
      $query->join($this->tlTableName, 'tl', 't.id = tl.entity_id');
      $query->join('taxonomy_term__field_language_code', 'tlc', 'tl.field_target_language_target_id = tlc.entity_id');
      $query->join('taxonomy_term_field_data', 'tfd', 'sl.field_source_language_target_id = tfd.tid');
      $query->addField('t', 'source_text__value', 'text');
      $query->addExpression('(LENGTH(t.nids) - LENGTH(REPLACE(t.nids, \',\', \'\')))', 'count');

      $text = $query->condition('t.target_text_plain', $sourceTextPlain)
      ->condition('tlc.field_language_code_value', $sourceLanguageCode)
      ->condition('tfd.name ', $targetLanguageCode)
      ->orderBy('count', 'desc')->orderBy('t.id', 'desc')
      ->range(0, 1)->execute()->fetchField();
    }

    return $text;
  }

  /**
  * To Store Translation data to the database.
  */
  public function setTranslationData($source, $target, $format, $tableData) {
    if(empty($tableData)) return;

    $sourceTextSpace = $this->convertTextForSpace($source);
    $sourceTextPlain = $this->convertTranslationText($source);
    $targetTextSpace = $this->convertTextForSpace($target);
    $targetTextPlain = $this->convertTranslationText($target);

    $tnid = $tableData['nid'];
    $sourceLanguage = $tableData['source_language'];
    $targetLanguage = $tableData['target_language'];
    $insertNid = ',' . $tnid . ',';
    $updateNid = $tnid . ',';

    $matchNid = '%' . $this->database->escapeLike($insertNid) . '%';
    $data = [
      'sourceText' => $source,
      'sourceTextSpace' => $sourceTextSpace,
      'sourceTextPlain' => $sourceTextPlain,
      'sourceLanguage' => $sourceLanguage,
      'targetText' => $target,
      'targetTextSpace' => $targetTextSpace,
      'targetTextPlain' => $targetTextPlain,
      'targetLanguage' => $targetLanguage,
      'insertNid' => $insertNid,
      'updateNid' => $updateNid,
    ];

    // Target text should not empty and must differ from source text.
    if(!empty($targetTextPlain) && ($sourceTextPlain != $targetTextPlain)) {
      // First Get the Matched record
      $query = $this->database->select($this->tableName, 't');
      $query->join($this->slTableName, 'sl', 't.id = sl.entity_id');
      $query->join($this->tlTableName, 'tl', 't.id = tl.entity_id');
      $query->fields('t', ['id', 'nids'])
        ->condition('source_text_plain', $sourceTextPlain)
        ->condition('target_text_plain', $targetTextPlain)
        ->condition('sl.field_source_language_target_id', $sourceLanguage)
        ->condition('tl.field_target_language_target_id', $targetLanguage)
        ->orderBy('id', 'desc');
      $matchedResult = $query->execute()->fetchAssoc();

      // Check if the target text is matched.
      if(!empty($matchedResult['id'])) {
        if(strpos($matchedResult['nids'], $insertNid) !== false) { //Check the Nid is exist.
          return;
        }
        else { // Remove nid from other translation and add it to the matched result.
          // Remove nid from the existing translation.
          $this->removeNidFromTranslationRecord($data);

          // Update nid to the matched result.
          $nids = $matchedResult['nids'] . $updateNid;
          $translationLoad = Translation::load($matchedResult['id']);
          $translationLoad->set('nids', $nids)
            ->save();
        }
      }
      // Matches not found.
      else {
        // Get the Nid based record.
        $query = $this->database->select($this->tableName, 't');
        $query->join($this->slTableName, 'sl', 't.id = sl.entity_id');
        $query->join($this->tlTableName, 'tl', 't.id = tl.entity_id');
        $query->fields('t', ['id', 'target_text_plain'])
          ->condition('source_text_plain', $sourceTextPlain)
          ->condition('sl.field_source_language_target_id', $sourceLanguage)
          ->condition('tl.field_target_language_target_id', $targetLanguage)
          ->condition('nids', $matchNid, 'LIKE')
          ->orderBy('id', 'desc');
        $nidMatchedResult = $query->execute()->fetchAssoc();

        // Check Nid matched record exist
        if(!empty($nidMatchedResult['id'])) {
          // If matching is more than 95% and the length differnce is less than 4. Update target text to the existing translation .

          similar_text($targetTextPlain, $nidMatchedResult['target_text_plain'], $percentage);
          $stringDiffCount = abs(strlen($targetTextPlain) - strlen($nidMatchedResult['target_text_plain']));

          if(($percentage >= 95) && ($stringDiffCount < 4)) {
            $translationLoad = Translation::load($nidMatchedResult['id']);
            $translationLoad->set('target_text', ['value' => $target, 'format' => $format])
              ->set('target_text_space', $targetTextSpace)
              ->set('target_text_plain', $targetTextPlain)
              ->save();
          }
          else { // Not similar to the existing record.
            $this->handleMatchedTranslationRecord($data, $format);
          }
        }
        else { // Nid matched record not exist.
          $this->handleMatchedTranslationRecord($data, $format);
        }
      }
    }
  }
}