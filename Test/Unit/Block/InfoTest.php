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
namespace TNW\Stripe\Test\Unit\Block;

use TNW\Stripe\Block\Info;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;

class InfoTest extends \PHPUnit\Framework\TestCase
{
    public function testGetLabel()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $context = $this->getMockBuilder(Context::class)
        ->disableOriginalConstructor()
        ->getMock();
        $config = $this->getMockBuilder(ConfigInterface::class)
        ->disableOriginalConstructor()
        ->getMock();

        $block = $objectManager->getObject(Info::class);

        $reflection = new \ReflectionClass(get_class($block));
        $method = $reflection->getMethod('getLabel');
        $method->setAccessible(true);

        $this->assertEquals(
            $method->invokeArgs($block, ['testing']),
            'testing'
        );
    }
}
