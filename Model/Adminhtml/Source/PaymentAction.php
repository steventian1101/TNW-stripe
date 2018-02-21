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
namespace TNW\Stripe\Model\Adminhtml\Source;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\Option\ArrayInterface;

class PaymentAction implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
        [
        'value' => AbstractMethod::ACTION_AUTHORIZE,
        'label' => __('Authorize Only')
        ],
      [
        'value' => AbstractMethod::ACTION_AUTHORIZE_CAPTURE,
        'label' => __('Authorize and Capture')
        ]
        ];
    }
}
