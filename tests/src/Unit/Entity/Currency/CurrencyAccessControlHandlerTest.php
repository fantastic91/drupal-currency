<?php

/**
 * @file
 * Contains \Drupal\Tests\currency\Unit\Entity\Currency\CurrencyAccessTest.
 */

namespace Drupal\Tests\currency\Unit\Entity\Currency;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\currency\Entity\Currency\CurrencyAccessControlHandler;
use Drupal\currency\Entity\CurrencyInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass Drupal\currency\Entity\Currency\CurrencyAccessControlHandler
 *
 * @group Currency
 */
class CurrencyAccessControlHandlerTest extends UnitTestCase {

  /**
   * Information about the entity type.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityType;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * The class under test.
   *
   * @var \Drupal\currency\Entity\Currency\CurrencyAccessControlHandler
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->entityType = $this->getMock(EntityTypeInterface::class);

    $this->moduleHandler = $this->getMock(ModuleHandlerInterface::class);

    $this->sut = new CurrencyAccessControlHandler($this->entityType, $this->moduleHandler);
  }

  /**
   * @covers ::createInstance
   * @covers ::__construct
   */
  function testCreateInstance() {
    $container = $this->getMock(ContainerInterface::class);
    $container->expects($this->once())
      ->method('get')
      ->with('module_handler')
      ->willReturn($this->moduleHandler);

    $sut = CurrencyAccessControlHandler::createInstance($container, $this->entityType);
    $this->assertInstanceOf(CurrencyAccessControlHandler::class, $sut);
  }

  /**
   * @covers ::checkAccess
   *
   * @dataProvider providerTestCheckAccess
   */
  function testCheckAccess($expected_value, $operation, $has_permission, $permission, $entity_status = FALSE, $currency_code = NULL) {
    $account = $this->getMock(AccountInterface::class);
    $account->expects($this->any())
      ->method('hasPermission')
      ->with($permission)
      ->willReturn((bool) $has_permission);

    $currency = $this->getMock(CurrencyInterface::class);
    $currency->expects($this->any())
      ->method('getCurrencyCode')
      ->willReturn($currency_code);
    $currency->expects($this->any())
      ->method('status')
      ->willReturn($entity_status);

    $this->moduleHandler->expects($this->any())
      ->method('invokeAll')
      ->willReturn([]);

    $method = new \ReflectionMethod($this->sut, 'checkAccess');
    $method->setAccessible(TRUE);

    $language_code = $this->randomMachineName();

    $this->assertSame($expected_value, $method->invoke($this->sut, $currency, $operation, $language_code, $account)->isAllowed());
  }

  /**
   * Provides data to self::testCheckAccess().
   */
  function providerTestCheckAccess() {
    return array(
      // The default currency cannot be deleted, even with permission.
      array(FALSE, 'delete', TRUE, 'currency.currency.delete', TRUE, 'XXX'),
      // A disabled currency cannot be disabled.
      array(FALSE, 'disable', TRUE, 'currency.currency.update', FALSE),
      // An enabled currency cannot be enabled.
      array(FALSE, 'enable', TRUE, 'currency.currency.update', TRUE),
      // A disabled currency cannot be enabled without permission.
      array(FALSE, 'disable', FALSE, 'currency.currency.update', TRUE),
      // A disabled currency cannot be enabled without permission.
      array(FALSE, 'enable', FALSE, 'currency.currency.update', FALSE),
      // A disabled currency can be enabled.
      array(TRUE, 'disable', TRUE, 'currency.currency.update', TRUE),
      // A disabled currency can be enabled.
      array(TRUE, 'enable', TRUE, 'currency.currency.update', FALSE),
      // A currency cannot be updated without permission.
      array(FALSE, 'update', FALSE, 'currency.currency.update'),
      // A currency can be updated with permission.
      array(TRUE, 'update', TRUE, 'currency.currency.update'),
      // A currency cannot be deleted without permission.
      array(FALSE, 'delete', FALSE, 'currency.currency.delete'),
      // A currency can be deleted with permission.
      array(TRUE, 'delete', TRUE, 'currency.currency.delete'),
    );
  }

  /**
   * @covers ::checkCreateAccess
   *
   * @dataProvider providerTestCheckCreateAccess
   */
  function testCheckCreateAccess($expected_value, $has_permission) {
    $account = $this->getMock(AccountInterface::class);
    $account->expects($this->once())
      ->method('hasPermission')
      ->with('currency.currency.create')
      ->willReturn($has_permission);
    $context = array();

    $method = new \ReflectionMethod($this->sut, 'checkCreateAccess');
    $method->setAccessible(TRUE);

    $this->assertSame($expected_value, $method->invoke($this->sut, $account, $context)->isAllowed());
  }

  /**
   * Provides data to self::testCheckCreateAccess().
   */
  function providerTestCheckCreateAccess() {
    return array(
      array(TRUE, TRUE),
      array(FALSE, FALSE),
    );
  }

}