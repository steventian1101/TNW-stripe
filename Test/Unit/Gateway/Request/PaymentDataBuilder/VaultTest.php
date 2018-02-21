<?php
/**
 * TNW_Stripe extension
 * NOTICE OF LICENSE
 *
 * This source file is subject to the OSL 3.0 License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 *
 * @category  TNW
 * @package   TNW_Stripe
 * @copyright Copyright (c) 2017-2018
 * @license   Open Software License (OSL 3.0)
 */
namespace TNW\Stripe\Test\Unit\Gateway\PaymentDataBuilder;

use TNW\Stripe\Gateway\Request\PaymentDataBuilder;
use TNW\Stripe\Gateway\Request\PaymentDataBuilder\Vault;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;
use TNW\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Sales\Api\Data\OrderPaymentExtension;
use Magento\Vault\Model\PaymentToken;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\AttributeInterface;
use TNW\Stripe\Gateway\Config\Config;

class VaultTest extends \PHPUnit\Framework\TestCase
{
  /** @var SubjectReader|MockObject */
    private $subjectReaderMock;

  /** @var PaymentDataObjectInterface|MockObject */
    private $paymentDataObjectMock;

  /** @var InfoInterface|MockObject */
    private $paymentMock;

  /** @var OrderAdapterInterface|MockObject */
    private $orderMock;

  /** @var OrderPaymentExtension|MockObject */
    private $extensionAttributeMock;

  /** @var PaymentToken|MockObject */
    private $paymentTokenMock;

  /** @var Session|MockObject */
    private $sessionMock;

  /** @var CustomerRepositoryInterface|MockObject */
    private $customerRepositoryMock;

  /** @var CustomerInterface|MockObject */
    private $customerMock;

  /** @var AttributeInterface|MockObject */
    private $customAttributeMock;

  /** @var Config|MockObject */
    private $configMock;

  /** @var  Vault */
    private $builder;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->paymentDataObjectMock = $this->getMockBuilder(PaymentDataObjectInterface::class)
        ->getMockForAbstractClass();

        $this->paymentMock = $this->getMockBuilder(InfoInterface::class)
        ->setMethods(['getExtensionAttributes'])
        ->getMockForAbstractClass();

        $this->extensionAttributeMock = $this->getMockBuilder(OrderPaymentExtension::class)
        ->disableOriginalConstructor()
        ->setMethods(['getVaultPaymentToken'])
        ->getMock();

        $this->paymentTokenMock = $this->getMockBuilder(PaymentToken::class)
        ->disableOriginalConstructor()
        ->setMethods(['getGatewayToken'])
        ->getMock();

        $this->orderMock = $this->getMockBuilder(OrderAdapterInterface::class)
        ->getMockForAbstractClass();

        $this->sessionMock = $this->getMockBuilder(Session::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->customerRepositoryMock = $this->getMockBuilder(CustomerRepositoryInterface::class)
        ->getMockForAbstractClass();

        $this->customerMock = $this->getMockBuilder(CustomerInterface::class)
        ->getMockForAbstractClass();

        $this->customAttributeMock = $this->getMockBuilder(AttributeInterface::class)
        ->getMockForAbstractClass();

        $this->configMock = $this->getMockBuilder(Config::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->builder = $objectManager->getObject(
            Vault::class,
            [
            'subjectReader' => $this->subjectReaderMock,
            'customerRepository' => $this->customerRepositoryMock,
            'customerSession' => $this->sessionMock,
            'config' => $this->configMock,
            ]
        );
    }

    public function testBuild()
    {
        $buildSubject = [
        'amount' => 10.00,
        'payment' => $this->paymentMock
        ];

        $expectedResult = [
        PaymentDataBuilder::AMOUNT => 1000,
        PaymentDataBuilder::ORDER_ID => '100000001',
        PaymentDataBuilder::CURRENCY => 'USD',
        PaymentDataBuilder::SOURCE => 'card_token',
        PaymentDataBuilder::CAPTURE => 'false',
        PaymentDataBuilder::CUSTOMER => 'cus_token'
        ];

        $this->subjectReaderMock->expects($this->once())
        ->method('readPayment')
        ->willReturn($this->paymentDataObjectMock);

        $this->paymentDataObjectMock->expects($this->once())
        ->method('getPayment')
        ->willReturn($this->paymentMock);

        $this->paymentDataObjectMock->expects($this->once())
        ->method('getOrder')
        ->willReturn($this->orderMock);

        $this->paymentMock->expects($this->once())
        ->method('getExtensionAttributes')
        ->willReturn($this->extensionAttributeMock);

        $this->extensionAttributeMock->expects($this->once())
        ->method('getVaultPaymentToken')
        ->willReturn($this->paymentTokenMock);

        $this->sessionMock->expects($this->once())
        ->method('getCustomerId')
        ->willReturn(1);

        $this->customerRepositoryMock->expects($this->once())
        ->method('getById')
        ->willReturn($this->customerMock);

        $this->customerMock->expects($this->once())
        ->method('getCustomAttribute')
        ->with('stripe_customer_id')
        ->willReturn($this->customAttributeMock);

        $this->customAttributeMock->expects($this->once())
        ->method('getValue')
        ->willReturn('cus_token');

        $this->subjectReaderMock->expects($this->once())
        ->method('readAmount')
        ->with($buildSubject)
        ->willReturn($buildSubject['amount']);

        $this->orderMock->expects($this->once())
        ->method('getOrderIncrementId')
        ->willReturn('100000001');

        $this->configMock->expects($this->once())
        ->method('getCurrency')
        ->willReturn('USD');

        $this->paymentTokenMock->expects($this->once())
        ->method('getGatewayToken')
        ->willReturn('card_token');

        $this->assertEquals(
            $expectedResult,
            $this->builder->build($buildSubject)
        );
    }
}
