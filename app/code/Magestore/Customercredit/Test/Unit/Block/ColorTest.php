<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @copyright   Copyright (c) 2017 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

namespace Magestore\Customercredit\Test\Unit\Block;

use PHPUnit\Framework\TestCase;

/**
 * Unit Test - Color Test
 */
class ColorTest extends TestCase
{
    /**
     * @var \Magestore\Customercredit\Block\Color
     */
    protected $block;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject('Magestore\Customercredit\Block\Color');
    }

    /**
     * Test Greeting
     */
    public function testGreeting()
    {
        $name = 'Foggyline';
        $this->assertEquals(
            'Hello '.$this->block->escapeHtml($name),
            $this->block->greeting($name)
        );
    }
}
