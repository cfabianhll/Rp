<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.1.12
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Helper;

use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;

class Mage extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;
    /**
     * @var \Magento\Backend\Model\Url
     */
    private $backendUrlManager;
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;

    /**
     * Mage constructor.
     * @param CollectionFactory $orderCollectionFactory
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Backend\Model\Url $backendUrlManager
     */
    public function __construct(
        CollectionFactory $orderCollectionFactory,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\Url $backendUrlManager
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->context                = $context;
        $this->backendUrlManager      = $backendUrlManager;

        parent::__construct($context);
    }

    /**
     * @param int $orderId
     * @return string
     */
    public function getBackendOrderUrl($orderId)
    {
        return $this->backendUrlManager->getUrl('sales/order/view', ['order_id' => $orderId]);
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrderCollection()
    {
        $collection = $this->orderCollectionFactory->getReport('sales_order_grid_data_source')->addFieldToSelect(
            [
                'entity_id',
                'increment_id',
                'customer_id',
                'created_at',
                'grand_total',
                'base_grand_total',
                'order_currency_code',
                'store_id',
                'billing_name',
                'shipping_name',
            ]
        );

        return $collection;
    }
}
