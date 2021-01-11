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
 */

namespace Magestore\Giftvoucher\Observer\RewardPoints;

use Magento\Framework\Event\ObserverInterface;
use Magestore\Giftvoucher\Model\Product\Type\Giftvoucher;

/**
 * Hide earning reward point message
 */
class EnableDisplayPointEarning implements ObserverInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * EnableDisplayPointEarning constructor.
     *
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->_coreRegistry = $registry;
    }

    /**
     * Hide earning point in store credit product detail page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $container = $observer->getEvent()->getData('container');
        $product = $this->_coreRegistry->registry('product');
        if ($container->getEnableDisplay()
            && $product
            && $product->getTypeId() == Giftvoucher::GIFT_CARD_TYPE) {
            $container->setEnableDisplay(false);
        }

        return $this;
    }
}
