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
namespace TNW\Stripe\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements ConfigProviderInterface
{
  const CODE = 'tnw_stripe';
  const CC_VAULT_CODE = 'tnw_stripe_vault';

  protected $_config;
  protected $_encryptor;

  public function __construct(
    ScopeConfigInterface $configInterface,
    EncryptorInterface $encryptorInterface
  ){
    $this->_config = $configInterface;
    $this->_encryptor = $encryptorInterface;
  }

  public function getConfig()
  {
    return [
      'payment' => [
        self::CODE => [
          'publishableKey' => $this->getPublishableKey(),
          'vaultCode' => self::CC_VAULT_CODE,
        ]
      ]
    ];
  }

  public function getPublishableKey() {
    if ($this->_isTestMode()) {
      return $this->_getEncryptedConfig('test_publishable_key');
    }
    return $this->_getEncryptedConfig('live_publishable_key');
  }

  protected function _isTestMode() {
    return $this->_getConfig('test_mode');
  }

  protected function _getEncryptedConfig($value) {
    $config = $this->_getConfig($value);
    return $this->_encryptor->decrypt($config);
  }

  protected function _getConfig($value) {
    return $this->_config->getValue('payment/tnw_stripe/' . $value, ScopeInterface::SCOPE_STORE);
  }
}