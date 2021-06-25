<?php

namespace Drupal\oscar_translation;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the translation type entity type.
 *
 * @see \Drupal\oscar_translation\Entity\TranslationType
 */
class TranslationTypeAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'access content');

      case 'delete':
        return parent::checkAccess($entity, $operation, $account)->addCacheableDependency($entity);
        break;

      default:
        return parent::checkAccess($entity, $operation, $account);

    }
  }

}
