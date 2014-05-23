<?php

/**
 * @file
 * Contains \Drupal\currency\Tests\Entity\CurrencyLocale\CurrencyLocaleDeleteFormUnitTest.
 */

namespace Drupal\currency\Tests\Entity\CurrencyLocale {

use Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleDeleteForm;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleDeleteForm
 */
class CurrencyLocaleDeleteFormUnitTest extends UnitTestCase {

  /**
   * The currency.
   *
   * @var \Drupal\currency\Entity\CurrencyLocaleInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currency;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The form under test.
   *
   * @var \Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleDeleteForm
   */
  protected $form;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleDeleteForm unit test',
      'group' => 'Currency',
    );
  }

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->currency = $this->getMockBuilder('\Drupal\currency\Entity\CurrencyLocale')
      ->disableOriginalConstructor()
      ->getMock();

    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');
    $this->stringTranslation->expects($this->any())
      ->method('translate')
      ->will($this->returnArgument(0));

    $this->form = new CurrencyLocaleDeleteForm($this->stringTranslation);
    $this->form->setEntity($this->currency);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $container->expects($this->once())
      ->method('get')
      ->with('string_translation')
      ->will($this->returnValue($this->stringTranslation));

    $form = CurrencyLocaleDeleteForm::create($container);
    $this->assertInstanceOf('\Drupal\currency\Entity\CurrencyLocale\CurrencyLocaleDeleteForm', $form);
  }

  /**
   * @covers ::getQuestion
   */
  function testGetQuestion() {
    $label = $this->randomName();
    $string = 'Do you really want to delete %label?';

    $this->currency->expects($this->once())
      ->method('label')
      ->will($this->returnValue($label));

    $this->stringTranslation->expects($this->once())
      ->method('translate')
      ->with($string, array(
        '%label' => $label,
      ));

    $this->assertSame($string, $this->form->getQuestion());
  }

  /**
   * @covers ::getConfirmText
   */
  function testGetConfirmText() {
    $string = 'Delete';

    $this->stringTranslation->expects($this->once())
      ->method('translate')
      ->with($string);

    $this->assertSame($string, $this->form->getConfirmText());
  }

  /**
   * @covers ::getCancelRoute
   */
  function testGetCancelRoute() {
    $url = $this->form->getCancelRoute();
    $this->assertInstanceOf('\Drupal\Core\Url', $url);
    $this->assertSame('currency.currency_locale.list', $url->getRouteName());
  }

  /**
   * @covers ::getFormid
   */
  function testGetFormid() {
    $this->assertSame('currency.currency_locale.delete', $this->form->getFormId());
  }

  /**
   * @covers ::submit
   */
  function testSubmit() {
    $this->currency->expects($this->once())
      ->method('delete');

    $form = array();
    $form_state = array();

    $this->form->submit($form, $form_state);
    $this->assertArrayHasKey('redirect_route', $form_state);
    /** @var \Drupal\Core\Url $url */
    $url = $form_state['redirect_route'];
    $this->assertInstanceOf('\Drupal\Core\Url', $url);
    $this->assertSame('currency.currency_locale.list', $url->getRouteName());
  }

}

}

namespace {

if (!function_exists('drupal_set_message')) {
  function drupal_set_message() {}
}

}
