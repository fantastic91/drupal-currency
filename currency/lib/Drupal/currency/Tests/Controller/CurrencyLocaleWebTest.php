<?php

/**
 * @file
 * Contains \Drupal\currency\Tests\Controller\CurrencyLocaleWebTest.
 */

namespace Drupal\currency\Tests\Controller;

use Drupal\simpletest\WebTestBase;

/**
 * Tests \Drupal\currency\Controller\CurrencyLocale.
 */
class CurrencyLocaleWebTest extends WebTestBase {

  public static $modules = array('currency');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\currency\Controller\CurrencyLocale web test',
      'group' => 'Currency',
    );
  }

  /**
   * Tests the user interface.
   */
  function testUserInterface() {
    $currency_locale_overview_path = 'admin/config/regional/currency-formatting/locale';
    $currency_formatting_path = 'admin/config/regional/currency-formatting';
    $regional_path = 'admin/config/regional';

    // Test the appearance of the link on the "Regional and language" page.
    $account = $this->drupalCreateUser(array('access administration pages'));
    $this->drupalLogin($account);
    $this->drupalGet($regional_path);
    $this->assertResponse('200');
    $this->assertNoLinkByHref($currency_formatting_path);
    $this->drupalGet($currency_formatting_path);
    $this->assertResponse('403');
    $account = $this->drupalCreateUser(array('currency.amount_formatting.administer', 'access administration pages'));
    $this->drupalLogin($account);
    $this->drupalGet($regional_path);
    $this->assertResponse('200');
    $this->assertLinkByHref($currency_formatting_path);
    $this->drupalGet($currency_formatting_path);
    $this->assertResponse('200');
    $this->drupalLogout();

    // Test the link to the currency locale overview.
    $account = $this->drupalCreateUser(array('currency.amount_formatting.administer'));
    $this->drupalLogin($account);
    $this->drupalGet($currency_formatting_path);
    $this->assertNoLinkByHref($currency_locale_overview_path);
    $account = $this->drupalCreateUser(array('currency.amount_formatting.administer', 'currency.currency_locale.view'));
    $this->drupalLogin($account);
    $this->drupalGet($currency_formatting_path);
    $this->assertLinkByHref($currency_locale_overview_path);
    $this->drupalLogout();

    // Test the currency locale overview.
    $this->drupalGet($currency_locale_overview_path);
    $this->assertResponse('403');
    $account = $this->drupalCreateUser(array('currency.currency_locale.view'));
    $this->drupalLogin($account);
    $this->drupalGet($currency_locale_overview_path);
    $this->assertText('Dutch (Netherlands)');
    $this->assertNoLink(t('Edit'));
    $this->assertNoLink(t('Delete'));
    $account = $this->drupalCreateUser(array('currency.currency_locale.view', 'currency.currency_locale.update', 'currency.currency_locale.delete'));
    $this->drupalLogin($account);
    $this->drupalGet($currency_locale_overview_path);
    $this->assertLinkByHref('admin/config/regional/currency-formatting/locale/nl_NL');
    $this->assertLinkByHref('admin/config/regional/currency-formatting/locale/nl_NL/delete');
    /** @var \Drupal\currency\LocaleDelegatorInterface $locale_delegator */
    $locale_delegator = \Drupal::service('currency.locale_delegator');
    // Make sure that there is an edit link, but no delete link for the default
    // currency locale.
    $this->assertLinkByHref('admin/config/regional/currency-formatting/locale/' . $locale_delegator::DEFAULT_LOCALE);
    $this->assertNoLinkByHref('admin/config/regional/currency-formatting/locale/' . $locale_delegator::DEFAULT_LOCALE . '/delete');

    // Test that the "Edit" operation link works.
    $this->clickLink(t('Edit'));
    $this->assertUrl('admin/config/regional/currency-formatting/locale/aa_DJ');
    $this->assertResponse('200');
    // Test that the "Delete" form action button works.
    // @todo Do not submit values once https://drupal.org/node/216064 has been
    //   fixed.
    $values = array(
      'decimal_separator' => $this->randomName(1),
      'language_code' => 'nl',
    );
    $this->drupalPostForm(NULL, $values, t('Delete'));
    $this->assertUrl('admin/config/regional/currency-formatting/locale/aa_DJ/delete');
    $this->assertResponse('200');

    // Test that the "Delete" operation link works.
    $this->drupalGet($currency_locale_overview_path);
    $this->clickLink(t('Delete'));
    $this->assertUrl('admin/config/regional/currency-formatting/locale/aa_DJ/delete');
    $this->assertResponse('200');
  }
}