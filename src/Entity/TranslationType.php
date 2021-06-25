<?php
namespace Drupal\oscar_translation\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
/**
 * Translation Type
 * 
 * @ConfigEntityType(
 *   id = "translation_type",
 *   label = @Translation("Translation Type"),
 *   bundle_of = "oscar_translation",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   config_prefix = "translation_type",
 *   config_export = {
 *     "id",
 *     "label",
 *   },
 *   handlers = {
 *     "access" = "Drupal\oscar_translation\TranslationTypeAccessControlHandler",
 *     "list_builder" = "Drupal\oscar_translation\TranslationTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\oscar_translation\Form\TranslationTypeEntityForm",
 *       "add" = "Drupal\oscar_translation\Form\TranslationTypeEntityForm",
 *       "edit" = "Drupal\oscar_translation\Form\TranslationTypeEntityForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer site configuration",
 *   links = {
 *     "canonical" = "/admin/structure/translation_type/{translation_type}",
 *     "add-form" = "/admin/structure/translation_type/add",
 *     "edit-form" = "/admin/structure/translation_type/{translation_type}/edit",
 *     "delete-form" = "/admin/structure/translation_type/{translation_type}/delete",
 *     "collection" = "/admin/structure/translation_type",
 *   }
 * )
 */
class TranslationType extends ConfigEntityBundleBase {}