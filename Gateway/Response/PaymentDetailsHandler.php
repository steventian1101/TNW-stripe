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
namespace TNW\Stripe\Gateway\Response;

use TNW\Stripe\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Helper\ContextHelper;
use TNW\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class PaymentDetailsHandler implements HandlerInterface
{
  const RISK_LEVEL = 'risk_level';
  const SELLER_MESSAGE = 'seller_message';
  const CAPTURE = 'captured';
  const TYPE = 'type';

  protected $additionalInformationMapping = [
    self::RISK_LEVEL,
    self::SELLER_MESSAGE,
    self::CAPTURE,
    self::TYPE
  ];

  private $subjectReader;

  public function __construct(
    SubjectReader $subjectReader
  ) {
    $this->subjectReader = $subjectReader;
  }

  public function handle(array $subject, array $response) {
    $paymentDataObject = $this->subjectReader->readPayment($subject);
    $transaction = $this->subjectReader->readTransaction($response);
    $payment = $paymentDataObject->getPayment();

    $payment->setCcTransId($transaction['id']);
    $payment->setLastTransId($transaction['id']);

    $outcome = $transaction['outcome']->__toArray();
    foreach ($this->additionalInformationMapping as $item) {
      if(!isset($outcome[$item])) {
        continue;
      }
      $payment->setAdditionalInformation($item, $outcome[$item]);
    }
  }
}