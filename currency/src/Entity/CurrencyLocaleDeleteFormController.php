<?php

/**
 * @file
 * Definition of Drupal\currency\Entity\CurrencyLocaleDeleteFormController.
 */

namespace Drupal\currency\Entity;

use Drupal\Core\Entity\EntityConfirmFormBase;

/**
 * Provides a currency currency locale delete form.
 */
class CurrencyLocaleDeleteFormController extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you really want to delete @label?', array(
      '@label' => $this->getEntity()->label(),
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return array(
      'route_name' => 'currency.currency_locale.list',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'currency.currency_locale.delete';
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    $currency_locale = $this->getEntity();
    $currency_locale->delete();
    drupal_set_message(t('The currency locale %label has been deleted.', array(
      '%label' => $currency_locale->label(),
    )));
    $form_state['redirect_route'] = $this->getCancelRoute();
  }
}