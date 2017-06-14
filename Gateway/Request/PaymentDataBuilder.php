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
namespace Pmclain\Stripe\Gateway\Request;

use Pmclain\Stripe\Gateway\Config\Config;
use Pmclain\Stripe\Observer\DataAssignObserver;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Pmclain\Stripe\Helper\Payment\Formatter;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Stripe\Customer;

class PaymentDataBuilder implements BuilderInterface
{
  use Formatter;
  
  const AMOUNT = 'amount';
  const SOURCE = 'source';
  const ORDER_ID = 'description';
  const CURRENCY = 'currency';
  const CAPTURE = 'capture';
  const CUSTOMER = 'customer';
  
  private $config;
  private $subjectReader;
  private $customerSession;
  private $customerRepository;
  
  public function __construct(
    Config $config,
    SubjectReader $subjectReader,
    Session $customerSession,
    CustomerRepositoryInterface $customerRepository
  ) {
    $this->config = $config;
    $this->subjectReader = $subjectReader;
    $this->customerSession = $customerSession;
    $this->customerRepository = $customerRepository;
  }
  
  public function build(array $subject) {
    $paymentDataObject = $this->subjectReader->readPayment($subject);
    $payment = $paymentDataObject->getPayment();
    $order = $paymentDataObject->getOrder();
    
    $result = [
      self::AMOUNT => $this->formatPrice($this->subjectReader->readAmount($subject)),
      self::ORDER_ID => $order->getOrderIncrementId(),
      self::CURRENCY => $this->config->getCurrency(),
      self::SOURCE => $this->getPaymentSource($payment),
      self::CAPTURE => 'false'
    ];

    if($this->isSavePaymentInformation($paymentDataObject)) {
      $stripeCustomerId = $this->getStripeCustomerId();
      if ($stripeCustomerId->getValue()) {
        $stripeCustomer = Customer::retrieve($stripeCustomerId->getValue());
        try {
          $card = $stripeCustomer->sources->create([
            'source' => $this->getPaymentSource($payment)
          ]);
        }catch (\Exception $e) {
          throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }

        $result[self::CUSTOMER] = $stripeCustomerId->getValue();
        $result[self::SOURCE] = $card->id;
      }
    }

    return $result;
  }

  protected function getStripeCustomerId() {
    $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
    $stripeCustomerId = $customer->getCustomAttribute('stripe_customer_id');

    if(!$stripeCustomerId) {
      $stripeCustomerId = $this->createNewStripeCustomer($customer->getEmail());
      $customer->setCustomAttribute('stripe_customer_id', $stripeCustomerId);

      $this->customerRepository->save($customer);
    }

    return $stripeCustomerId;
  }

  protected function createNewStripeCustomer($email) {
    $result = Customer::create([
      'description' => 'Customer for ' . $email,
    ]);

    return $result->id;
  }

  protected function isSavePaymentInformation($paymentDataObject) {
    $payment = $paymentDataObject->getPayment();

    return $payment->getAdditionalInformation('is_active_payment_token_enabler');
  }

  protected function getPaymentSource($payment) {
    if($token = $payment->getAdditionalInformation('cc_token')) {
      return $token;
    }
    return [
      'exp_month' => $payment->getCcExpMonth(),
      'exp_year' => $payment->getCcExpYear(),
      'number' => $payment->getCcNumber(),
      'object' => 'card',
      'cvc' => $payment->getCcCid(),
    ];
  }
}