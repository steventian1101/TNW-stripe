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
namespace TNW\Stripe\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class DataAssignObserver extends AbstractDataAssignObserver
{
  /**
   * @param Observer $observer
   * @return void
   */
  public function execute(Observer $observer)
  {
    $method = $this->readMethodArgument($observer);
    $data = $this->readDataArgument($observer);
    $paymentInfo = $method->getInfoInstance();
    if (key_exists('cc_token', $data->getDataByKey('additional_data'))) {
      $paymentInfo->setAdditionalInformation(
        'cc_token',
        $data->getDataByKey('additional_data')['cc_token']
      );
    }
  }
}