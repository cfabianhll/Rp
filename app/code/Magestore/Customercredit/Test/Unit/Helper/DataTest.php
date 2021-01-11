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

namespace Magestore\Customercredit\Test\Unit\Helper;

use PHPUnit\Framework\TestCase;

/**
 * Unit Test - Data Test
 */
class DataTest extends TestCase
{
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $helper;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->helper = $objectManager->getObject('Magestore\Customercredit\Helper\Data');
    }

    /**
     * Test Calc
     */
    public function testCalc()
    {

        $result = $this->helper->calc(7, 3);
        $expected = 10;
        $this->assertEquals($expected, $result);
    }
}
