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
namespace TNW\Stripe\Block;

use TNW\Stripe\Gateway\Config\Config as GatewayConfig;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form\Cc;
use Magento\Payment\Model\Config;
use Magento\Payment\Helper\Data as Helper;
use TNW\Stripe\Model\Ui\ConfigProvider;

class Form extends Cc
{
    /** @var GatewayConfig $gatewayConfig */
    protected $gatewayConfig;

    /** @var Helper $paymentDataHelper */
    private $paymentDataHelper;

    public function __construct(
        Context $context,
        Config $paymentConfig,
        GatewayConfig $gatewayConfig,
        Helper $helper,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->gatewayConfig = $gatewayConfig;
        $this->paymentDataHelper = $helper;
    }

    public function useCcv()
    {
        return $this->gatewayConfig->isCcvEnabled();
    }

    /**
     * Check if vault enabled
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isVaultEnabled()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $vaultPayment = $this->getVaultPayment();
        return $vaultPayment->isActive($storeId);
    }

    /**
     * Get configured vault payment for Braintree
     * @return \Magento\Payment\Model\MethodInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getVaultPayment()
    {
        return $this->paymentDataHelper->getMethodInstance(ConfigProvider::CC_VAULT_CODE);
    }
}
