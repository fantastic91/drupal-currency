<?php

/**
 * @file
 * Contains \Drupal\currency\Plugin\Filter\CurrencyLocalize.
 */

namespace Drupal\currency\Plugin\Filter;

use Commercie\Currency\InputInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter to format amounts.
 *
 * @Filter(
 *   cache = false,
 *   id = "currency_localize",
 *   module = "currency",
 *   title = @Translation("Currency amount formatting"),
 *   type = \Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE
 * )
 */
class CurrencyLocalize extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The currency storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $currencyStorage;

  /**
   * The input parser.
   *
   * @var \Commercie\Currency\InputInterface
   */
  protected $input;

  /**
   * Constructs a new class instance
   *
   * @param mixed[] $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed[] $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\StringTranslation\TranslationInterface
   *   The string translator.
   * @param \Drupal\Core\Entity\EntityStorageInterface $currency_storage
   *   The currency entity storage.
   * @param \Commercie\Currency\InputInterface $input
   *   The input parser.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, TranslationInterface $string_translation, EntityStorageInterface $currency_storage, InputInterface $input) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currencyStorage = $currency_storage;
    $this->input = $input;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('string_translation'), $entity_manager->getStorage('currency'), $container->get('currency.input'));
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    return preg_replace_callback('/\[currency-localize:([a-z]{3}):(.+?)\]/i', [$this, 'processCallback'], $text);
  }

  /**
   * Implements preg_replace_callback() callback.
   *
   * @see self::process()
   */
  function processCallback(array $matches) {
    $currency_code = $matches[1];
    $amount = $this->input->parseAmount($matches[2]);
    // The amount is invalid, so return the token.
    if (!$amount) {
      return $matches[0];
    }
    /** @var \Drupal\currency\Entity\CurrencyInterface $currency */
    $currency = $this->currencyStorage->load($currency_code);
    if ($currency) {
      return $currency->formatAmount($amount);
    }
    // The currency code is invalid, so return the token.
    return $matches[0];
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('Use <code>[currency-localize:<strong>currency-code</strong>:<strong>amount</strong>]</code> to localize an amount of money. The <code>currency-code</code> and <code>amount</code> parameters are the ISO 4217 currency code and the actual amount to display. Example: <code>[currency-localize:EUR:99.95]</code>.');
  }

}
