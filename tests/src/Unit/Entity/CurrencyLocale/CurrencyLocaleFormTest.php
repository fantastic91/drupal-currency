<?php

/**
 * @file
 * Contains \Drupal\Tests\currency\Unit\Entity\CurrencyLocale\CurrencyLocaleFormTest.
 */

namespace Drupal\Tests\currency\Unit\Entity\CurrencyLocale {

  use Drupal\Core\Entity\EntityManagerInterface;
  use Drupal\Core\Entity\EntityStorageInterface;
  use Drupal\Core\Form\FormStateInterface;
  use Drupal\Core\Form\FormValidatorInterface;
  use Drupal\Core\Language\LanguageInterface;
  use Drupal\Core\Locale\CountryManagerInterface;
  use Drupal\Core\Utility\LinkGeneratorInterface;
  use Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleForm;
  use Drupal\currency\Entity\CurrencyLocaleInterface;
  use Drupal\Tests\UnitTestCase;
  use Symfony\Component\DependencyInjection\ContainerBuilder;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * @coversDefaultClass \Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleForm
   *
   * @group Currency
   */
  class CurrencyLocaleFormTest extends UnitTestCase {

    /**
     * The country manager.
     *
     * @var \Drupal\Core\Locale\CountryManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $countryManager;

    /**
     * The currency locale.
     *
     * @var \Drupal\currency\Entity\CurrencyLocaleInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyLocale;

    /**
     * The currency locale storage.
     *
     * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyLocaleStorage;

    /**
     * The link generator.
     *
     * @var \Drupal\Core\Utility\LinkGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkGenerator;

    /**
     * The string translation service.
     *
     * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stringTranslation;

    /**
     * The form under test.
     *
     * @var \Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleForm
     */
    protected $form;

    /**
     * The form validator.
     *
     * @var \Drupal\Core\Form\FormValidatorInterface
     */
    protected $formValidator;

    /**
     * {@inheritdoc}
     */
    public function setUp() {
      $this->countryManager = $this->getMock(CountryManagerInterface::class);

      $this->currencyLocale = $this->getMock(CurrencyLocaleInterface::class);

      $this->currencyLocaleStorage = $this->getMock(EntityStorageInterface::class);

      $this->linkGenerator = $this->getMock(LinkGeneratorInterface::class);

      $this->stringTranslation = $this->getStringTranslationStub();

      $this->formValidator = $this->getMock(FormValidatorInterface::class);

      $container = new ContainerBuilder();
      $container->set('form_validator', $this->formValidator);
      \Drupal::setContainer($container);

      $this->form = new CurrencyLocaleForm($this->stringTranslation, $this->linkGenerator, $this->currencyLocaleStorage, $this->countryManager);
      $this->form->setEntity($this->currencyLocale);
    }

    /**
     * @covers ::create
     * @covers ::__construct
     */
    function testCreate() {
      $entity_manager = $this->getMock(EntityManagerInterface::class);
      $entity_manager->expects($this->once())
        ->method('getStorage')
        ->with('currency_locale')
        ->willReturn($this->currencyLocaleStorage);

      $container = $this->getMock(ContainerInterface::class);

      $map = array(
        array('country_manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->countryManager),
        array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_manager),
        array('link_generator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->linkGenerator),
        array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
      );
      $container->expects($this->any())
        ->method('get')
        ->willReturnMap($map);

      $form = CurrencyLocaleForm::create($container);
      $this->assertInstanceOf(CurrencyLocaleForm::class, $form);
    }

    /**
     * @covers ::copyFormValuesToEntity
     */
    public function testCopyFormValuesToEntity() {
      $language_code = $this->randomMachineName();
      $country_code = $this->randomMachineName();
      $pattern = $this->randomMachineName();
      $decimal_separator = $this->randomMachineName();
      $grouping_separator = $this->randomMachineName();

      $this->currencyLocale->expects($this->once())
        ->method('setLocale')
        ->with($language_code, $country_code);
      $this->currencyLocale->expects($this->once())
        ->method('setPattern')
        ->with($pattern);
      $this->currencyLocale->expects($this->once())
        ->method('setDecimalSeparator')
        ->with($decimal_separator);
      $this->currencyLocale->expects($this->once())
        ->method('setGroupingSeparator')
        ->with($grouping_separator);

      $form = array();
      $form_state = $this->getMock(FormStateInterface::class);
      $form_state->expects($this->atLeastOnce())
        ->method('getValues')
        ->willReturn(array(
          'language_code' => $language_code,
          'country_code' => $country_code,
          'pattern' => $pattern,
          'decimal_separator' => $decimal_separator,
          'grouping_separator' => $grouping_separator,
        ));

      $method = new \ReflectionMethod($this->form, 'copyFormValuesToEntity');
      $method->setAccessible(TRUE);

      $method->invokeArgs($this->form, array($this->currencyLocale, $form, $form_state));
    }

    /**
     * @covers ::form
     */
    public function testForm() {
      $language_code = $this->randomMachineName();
      $country_code = $this->randomMachineName();
      $pattern = $this->randomMachineName();
      $decimal_separator = $this->randomMachineName();
      $grouping_separator = $this->randomMachineName();

      $this->currencyLocale->expects($this->once())
        ->method('getLanguageCode')
        ->willReturn($language_code);
      $this->currencyLocale->expects($this->once())
        ->method('getCountryCode')
        ->willReturn($country_code);
      $this->currencyLocale->expects($this->once())
        ->method('getPattern')
        ->willReturn($pattern);
      $this->currencyLocale->expects($this->once())
        ->method('getDecimalSeparator')
        ->willReturn($decimal_separator);
      $this->currencyLocale->expects($this->once())
        ->method('getGroupingSeparator')
        ->willReturn($grouping_separator);

      $language = $this->getMock(LanguageInterface::class);

      $this->currencyLocale->expects($this->any())
        ->method('language')
        ->willReturn($language);

      $country_list = array(
        $this->randomMachineName() => $this->randomMachineName(),
      );
      $this->countryManager->expects($this->atLeastOnce())
        ->method('getList')
        ->willReturn($country_list);

      $form = array();
      $form_state = $this->getMock(FormStateInterface::class);

      $expected = array(
        'language_code' => array(
          '#default_value' => $language_code,
          '#empty_value' => '',
          '#options' => array(
            'af' => 'Afrikaans',
                'sq' => 'Albanian',
                'am' => 'Amharic',
                'ar' => 'Arabic',
                'hy' => 'Armenian',
                'ast' => 'Asturian',
                'az' => 'Azerbaijani',
                'ms' => 'Bahasa Malaysia',
                'eu' => 'Basque',
                'be' => 'Belarusian',
                'bn' => 'Bengali',
                'bs' => 'Bosnian',
                'bg' => 'Bulgarian',
                'my' => 'Burmese',
                'ca' => 'Catalan',
                'zh-hans' => 'Chinese, Simplified',
                'zh-hant' => 'Chinese, Traditional',
                'hr' => 'Croatian',
                'cs' => 'Czech',
                'da' => 'Danish',
                'nl' => 'Dutch',
                'dz' => 'Dzongkha',
                'en' => 'English',
                'eo' => 'Esperanto',
                'et' => 'Estonian',
                'fo' => 'Faeroese',
                'fil' => 'Filipino',
                'fi' => 'Finnish',
                'fr' => 'French',
                'fy' => 'Frisian, Western',
                'gl' => 'Galician',
                'ka' => 'Georgian',
                'de' => 'German',
                'el' => 'Greek',
                'gu' => 'Gujarati',
                'ht' => 'Haitian Creole',
                'he' => 'Hebrew',
                'hi' => 'Hindi',
                'hu' => 'Hungarian',
                'is' => 'Icelandic',
                'id' => 'Indonesian',
                'ga' => 'Irish',
                'it' => 'Italian',
                'ja' => 'Japanese',
                'jv' => 'Javanese',
                'kn' => 'Kannada',
                'kk' => 'Kazakh',
                'km' => 'Khmer',
                'ko' => 'Korean',
                'ku' => 'Kurdish',
                'ky' => 'Kyrgyz',
                'lo' => 'Lao',
                'lv' => 'Latvian',
                'lt' => 'Lithuanian',
                'xx-lolspeak' => 'Lolspeak',
                'mk' => 'Macedonian',
                'mg' => 'Malagasy',
                'ml' => 'Malayalam',
                'mr' => 'Marathi',
                'mn' => 'Mongolian',
                'ne' => 'Nepali',
                'se' => 'Northern Sami',
                'nb' => 'Norwegian Bokmål',
                'nn' => 'Norwegian Nynorsk',
                'oc' => 'Occitan',
                'fa' => 'Persian, Farsi',
                'pl' => 'Polish',
                'pt-br' => 'Portuguese, Brazil',
                'pt-pt' => 'Portuguese, Portugal',
                'pa' => 'Punjabi',
                'ro' => 'Romanian',
                'ru' => 'Russian',
                'sco' => 'Scots',
                'gd' => 'Scots Gaelic',
                'sr' => 'Serbian',
                'si' => 'Sinhala',
                'sk' => 'Slovak',
                'sl' => 'Slovenian',
                'es' => 'Spanish',
                'sw' => 'Swahili',
                'sv' => 'Swedish',
                'gsw-berne' => 'Swiss German',
                'ta' => 'Tamil',
                'ta-lk' => 'Tamil, Sri Lanka',
                'te' => 'Telugu',
                'th' => 'Thai',
                'bo' => 'Tibetan',
                'tr' => 'Turkish',
                'tyv' => 'Tuvan',
                'uk' => 'Ukrainian',
                'ur' => 'Urdu',
                'ug' => 'Uyghur',
                'vi' => 'Vietnamese',
                'cy' => 'Welsh',
          ),
          '#required' => TRUE,
          '#title' => 'Language',
          '#type' => 'select',
        ),
        'country_code' => array(
          '#default_value' => $country_code,
          '#empty_value' => '',
          '#options' => $country_list,
          '#required' => TRUE,
          '#title' => 'Country',
          '#type' => 'select',
        ),
        'formatting' => array(
          '#open' => TRUE,
          '#title' => 'Formatting',
          '#type' => 'details',
          'decimal_separator' => array(
            '#default_value' => $decimal_separator,
            '#maxlength' => 255,
            '#required' => TRUE,
            '#size' => 3,
            '#title' => 'Decimal separator',
            '#type' => 'textfield',
          ),
          'grouping_separator' => array(
            '#default_value' => $grouping_separator,
            '#maxlength' => 255,
            '#size' => 3,
            '#title' => 'Group separator',
            '#type' => 'textfield',
          ),
          'pattern' => array(
            '#default_value' => $pattern,
            '#description' => 'A Unicode <abbr title="Common Locale Data Repository">CLDR</abbr> <a href="http://cldr.unicode.org/translation/number-patterns">currency number pattern</a>.',
            '#maxlength' => 255,
            '#title' => 'Pattern',
            '#type' => 'textfield',
          ),
        ),
        '#after_build' => ['::afterBuild'],
      );
      $build = $this->form->form($form, $form_state);
      unset($build['langcode']);
      unset($build['#process']);
      $this->assertSame($expected, $build);
    }

    /**
     * @covers ::save
     */
    public function testSave() {
      $this->currencyLocale->expects($this->once())
        ->method('save');

      $form = array();
      $form_state = $this->getMock(FormStateInterface::class);
      $form_state->expects($this->once())
        ->method('setRedirect')
        ->with('entity.currency_locale.collection');

      $this->form->save($form, $form_state);
    }

    /**
     * @covers ::validateForm
     * @dataProvider providerTestValidate
     */
    public function testValidateForm($input_value_language_code, $input_value_country_code, $locale, $currency_locale_is_new, $locale_is_used) {
      $form = array(
        'locale' => array(
          '#foo' => $this->randomMachineName(),
        ),
      );
      $form_state = $this->getMock(FormStateInterface::class);
      $form_state->expects($this->any())
        ->method('getValues')
        ->willReturn(array(
          'country_code' => $input_value_country_code,
          'language_code' => $input_value_language_code,
        ));

      $this->currencyLocale->expects($this->atLeastOnce())
        ->method('isNew')
        ->willReturn($currency_locale_is_new);

      if ($currency_locale_is_new) {
        if ($locale_is_used) {
          $loaded_currency_locale = $this->getMock(CurrencyLocaleInterface::class);

          $this->currencyLocaleStorage->expects($this->once())
            ->method('load')
            ->with($locale)
            ->willReturn($loaded_currency_locale);

          $form_state->expects($this->once())
            ->method('setError')
            ->with($form['locale'], 'A pattern for this locale already exists.');
        }
        else {
          $this->currencyLocaleStorage->expects($this->once())
            ->method('load')
            ->with($locale)
            ->willReturn(FALSE);

          $form_state->expects($this->never())
            ->method('setError');
        }
      }
      else {
        $this->currencyLocaleStorage->expects($this->never())
          ->method('load');

        $form_state->expects($this->never())
          ->method('setError');
      }

      $this->form->validateForm($form, $form_state);
    }

    /**
     * Provides data to self::testValidate()
     */
    public function providerTestValidate() {
      return array(
        array('Nl', 'nL', 'nl_NL', FALSE, FALSE),
        array('UK', 'ua', 'uk_UA', TRUE, FALSE),
        array('nl', 'NL', 'nl_NL', TRUE, TRUE),
        array('uk', 'UA', 'uk_UA', FALSE, TRUE),
      );
    }

  }

}

namespace {

  if (!function_exists('drupal_set_message')) {
    function drupal_set_message() {}
  }

}
