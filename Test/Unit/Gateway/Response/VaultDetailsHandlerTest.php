<?php
/**
 * Pmclain_Stripe extension
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GPL v3 License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl.txt
 *
 * @category  Pmclain
 * @package   Pmclain_Stripe
 * @copyright Copyright (c) 2017
 * @license   https://www.gnu.org/licenses/gpl.txt GPL v3 License
 */
namespace Pmclain\Stripe\Test\Unit\Gateway\Response;

use Pmclain\Stripe\Gateway\Response\VaultDetailsHandler;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Stripe\Charge;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Stripe\Card;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Model\CreditCardTokenFactory;
use Magento\Vault\Model\PaymentToken;
use Pmclain\Stripe\Gateway\Config\Config;
use Magento\Sales\Api\Data\OrderPaymentExtension;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;

class VaultDetailsHandlerTest extends \PHPUnit\Framework\TestCase
{
  /** @var PaymentDataObject|MockObject */
  private $paymentDataObjectMock;

  /** @var Charge|MockObject */
  private $chargeMock;

  /** @var SubjectReader|MockObject */
  private $subjectReaderMock;

  /** @var Card|MockObject */
  private $cardMock;

  /** @var Payment|MockObject */
  private $paymentMock;

  /** @var CreditCardTokenFactory|MockObject */
  private $paymentTokenFactoryMock;

  /** @var PaymentToken|MockObject */
  private $paymentTokenMock;

  /** @var Config|MockObject */
  private $configMock;

  /** @var OrderPaymentExtension|MockObject */
  private $extensionAttributeMock;

  /** @var OrderPaymentExtensionInterfaceFactory|MockObject */
  private $extensionAttributeFactoryMock;

  /** @var  VaultDetailsHandler */
  private $handler;

  protected function setUp() {
    $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

    $this->paymentDataObjectMock = $this->getMockBuilder(PaymentDataObject::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->chargeMock = $this->getMockBuilder(Charge::class)
      ->getMock();

    $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->cardMock = $this->getMockBuilder(Card::class)
      ->setMethods(['__toArray'])
      ->getMock();

    $this->paymentMock = $this->getMockBuilder(Payment::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentTokenFactoryMock = $this->getMockBuilder(CreditCardTokenFactory::class)
      ->disableOriginalConstructor()
      ->setMethods(['create'])
      ->getMock();

    $this->paymentTokenMock = $this->getMockBuilder(PaymentToken::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->configMock = $this->getMockBuilder(Config::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->extensionAttributeMock = $this->getMockBuilder(OrderPaymentExtension::class)
      ->disableOriginalConstructor()
      ->setMethods(['setVaultPaymentToken'])
      ->getmock();

    $this->extensionAttributeFactoryMock = $this->getMockBuilder(OrderPaymentExtensionInterfaceFactory::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->handler = $objectManager->getObject(
      VaultDetailsHandler::class,
      [
        'subjectReader' => $this->subjectReaderMock,
        'paymentTokenFactory' => $this->paymentTokenFactoryMock,
        'config' => $this->configMock,
        'paymentExtensionFactory' => $this->extensionAttributeFactoryMock,
      ]
    );
  }

  public function testHandle() {
    $handlingSubject = [
      'payment' => $this->paymentDataObjectMock,
      'amount' => 10.00
    ];

    $response = [
      [$this->chargeMock]
    ];

    $this->subjectReaderMock->expects($this->once())
      ->method('readPayment')
      ->with($handlingSubject)
      ->willReturn($this->paymentDataObjectMock);

    $this->subjectReaderMock->expects($this->once())
      ->method('readTransaction')
      ->with($response)
      ->willReturn(['source' => $this->cardMock]);

    $this->paymentDataObjectMock->expects($this->once())
      ->method('getPayment')
      ->willReturn($this->paymentMock);

    $this->paymentMock->expects($this->once())
      ->method('getAdditionalInformation')
      ->with('is_active_payment_token_enabler')
      ->willReturn(true);

    $this->cardMock->expects($this->once())
      ->method('__toArray')
      ->willReturn([
        'id' => 'card_token',
        'brand' => 'Visa',
        'last4' => '4444',
        'exp_month' => '01',
        'exp_year' => '2018'
      ]);

    $this->paymentTokenFactoryMock->expects($this->once())
      ->method('create')
      ->willReturn($this->paymentTokenMock);

    $this->configMock->expects($this->once())
      ->method('getCcTypesMapper')
      ->willReturn(['visa' => 'VI']);

    $this->paymentMock->expects($this->once())
      ->method('getExtensionAttributes')
      ->willReturn($this->extensionAttributeMock);

    $this->extensionAttributeMock->expects($this->once())
      ->method('setVaultPaymentToken')
      ->with($this->paymentTokenMock);

    $this->handler->handle($handlingSubject, $response);
  }
}