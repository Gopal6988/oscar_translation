<?php

namespace Drupal\oscar_translation\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\oscar_translation\Service\TranslationService;
use Drupal\oscar_translation\Entity\Translation;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\InvokeCommand;

/**
 * Content translation form.
 */
class AjaxTranslationListForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('oscar_translation.translation')
    );
  }

  /**
   * Construct a form.
   *
   */
  public function __construct($translation) {
    $this->translationService = $translation;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'oscar_translation_list_form';
  }

  /**
   * Builds the Source based ajax Translation list form.
   *
   * @param array $form
   *   Render array representing from.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Current form state.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  public function buildForm(array $form, FormStateInterface $formState) {
    $buildInfo = $formState->getBuildInfo();

    list($ids) = $buildInfo['args'];
    $data = $this->translationService->getAllTranslationSuggestionByIds($ids);
    $sourceRequest = $this->getRequest()->query->get('source');

    $header = [
      'Suggestions',
    ];
    $options = [];
    $source = '';
    foreach($data as $k => $v) {
      $sourceValue = $v->get('source_text')->getValue();
      $targetValue = $v->get('target_text')->getValue();

      if($sourceRequest == 'true') {
        $source = $sourceValue[0]['value'];
        $value = $targetValue[0]['value'];
      }
      else {
        $source = $targetValue[0]['value'];
        $value = $sourceValue[0]['value'];
      }

      $value = '<div class="suggestion-table-list-data">' . $value . '</div>';
      $options[$k] = [
        ['data' => ['#markup' => $value]],
      ];
    }

    $form['#prefix'] = '<div id="modal-suggestion-table-list">';
    $form['#suffix'] = '</div>';

    $form['messages'] = [
      '#weight' => -9999,
      '#type' => 'status_messages',
    ];

    $form['description'] = [
      '#markup' => $this->t('<div class="translation-suggestion-list-description">Below are some suggested translations for this field. If any fit the context of the source text, please choose one and click Insert.</div>'),
    ];
    $form['source_text'] = [
      '#markup' => '<div class="translation-suggestion-list-sourcetext"><strong>Source Text: </strong>' .  $source . '</div>',
    ];
    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#multiple' => FALSE,
      '#empty' => $this->t('Translation not available.'),
      '#prefix' => '<div id="suggestion-table-list" class="suggestion-table-list">',
      '#suffix' => '</div>',
    ];
    $form['source'] = [
      '#type' => 'hidden',
      '#value' => $sourceRequest,
    ];

    $form['pager'] = [
      '#type' => 'pager',
      '#route_name' => 'base.ajax.translation.list',
      '#route_parameters' => ['ids' => $ids],
    ];

    $form['actions'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['translation-suggestion-actions-container']],
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Insert',
      '#ajax' => [
        'callback' => '::AjaxTranslationListFormSubmit',
        'wrapper' => 'suggestion-table-list',
      ],
      '#attributes' => [
        'class' => ['translation-suggestion-list-submit-button'],
      ],
    ];
    $form['actions']['static_submit'] = [
      '#markup' => '<div class="translation-suggestion-list-staic-submit-button button--primary button">Insert</div>',
    ];
    $form['actions']['cancel'] = [
      '#markup' => '<div class="translation-suggestion-list-cancel-button button">Cancel</div>',
    ];

    return $form;
  }

  /**
   * Form submission handler.
   */
  public function validateForm(array &$form, FormStateInterface $formState) {
    $values = $formState->getValues();
    if(empty($values['table'])) {
      $formState->setErrorByName('table', 'You have to choose one value on clicking Insert Translation.');
    }
  }

  /**
   * Form submission handler.
   */
  public function submitForm(array &$form, FormStateInterface $formState) {

  }

  /**
   * Ajax Form submission handler.
   */
  public function AjaxTranslationListFormSubmit(array &$form, FormStateInterface $formState) {
    $response = new AjaxResponse();

    if($formState->hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#modal-suggestion-table-list', $form));
      return $response;
    }
    $values = $formState->getValues();
    $id = $values['table'];
    $source = $values['source'];

    $translation = Translation::load($id);
    $format = $translation->get('source_text')->format;

    if($source == 'true') {
      $value = $translation->get('target_text')->value;
    }
    else {
      $value = $translation->get('source_text')->value;
    }

    if($format == 'plain_text') {
      $response->addCommand(new InvokeCommand(NULL, 'plainTextTranslationSubmission', [$value]));
    }
    else {
      $response->addCommand(new InvokeCommand(NULL, 'formatTextTranslationSubmission', [$value]));
    }

    return $response;
  }
}