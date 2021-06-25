<?php

namespace Drupal\oscar_translation\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Form controller for the oscar_translation entity edit forms.
 *
 * @ingroup oscar_translation
 */
class TranslationEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $routeParameters = $this->getRouteMatch();

    $entity = $this->entity;
    $entityId = $entity->id();

    $submitValue = 'Add Translation';
    if(!empty($entityId)) {
      $submitValue = 'Update Translation';

      $form['source_text']['widget'][0]['#disabled'] = TRUE;
      $form['field_source_language']['widget']['#disabled'] = TRUE;
      $form['field_target_language']['widget']['#disabled'] = TRUE;
    }

    $format = $routeParameters->getParameter('format');
    if(!empty($format)) {
      $form['source_text']['widget'][0]['#format'] = $format;
      $form['target_text']['widget'][0]['#format'] = $format;
    }

    $form['actions']['submit']['#value'] = $submitValue;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // If errors return the validation.
    $errors = $form_state->hasAnyErrors();
    if($errors) return;

    $entity = $this->entity;
    $id = $entity->id();
    $values = $form_state->getValues();
    $isTranslationExists = $entity->translationService->checkTranslationExists($values, $id);

    if($isTranslationExists) {
      $url = Url::fromUserInput('/translation-list');
      $link = Link::fromTextAndUrl('Translation List', $url)->toString();
      $message = t('Translation already exists, search in the @translation_link to access the existing translation', ['@translation_link' => $link]);
      $form_state->setErrorByName('target_text][0][value', $message);
    }
    else {
      $sourceText = $values['source_text'][0]['value'];
      $targetText = $values['target_text'][0]['value'];

      $targetTextSpace = $entity->translationService->convertTextForSpace($targetText);
      $targetTextPlain = $entity->translationService->convertTranslationText($targetText);

      $form_state->setValue('target_text_space', $targetTextSpace);
      $form_state->setValue('target_text_plain', $targetTextPlain);

      if($entity->isNew()) {
        $nids = ',';
        $title = $entity->translationService->convertTextForTitle($sourceText);
        $sourceTextSpace = $entity->translationService->convertTextForSpace($sourceText);
        $sourceTextPlain = $entity->translationService->convertTranslationText($sourceText);

        $form_state->setValue('title', $title);
        $form_state->setValue('source_text_space', $sourceTextSpace);
        $form_state->setValue('source_text_plain', $sourceTextPlain);
        $form_state->setValue('nids', $nids);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);

    $entity = $this->entity;
    if ($status == SAVED_UPDATED) {
      $this->messenger()
        ->addMessage($this->t('The translation %feed has been updated.', ['%feed' => $entity->toLink()->toString()]));
    } else {
      $this->messenger()
        ->addMessage($this->t('The translation %feed has been added.', ['%feed' => $entity->toLink()->toString()]));
    }

    $url = Url::fromRoute('view.translation_list.page_1');
    $form_state->setRedirectUrl($url);
    return $status;
  }
}
