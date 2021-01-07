<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Catalog\Product;

use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Registry;

/**
 * Class AfterRenderer
 */
class AfterRenderer extends Template
{

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var System
     */
    private $system;

    /**
     * @var Item
     */
    private $stockResource;

    /**
     * AssignProducts constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Item $stockResource
     * @param System $system
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Item $stockResource,
        System $system,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->system = $system;
        $this->stockResource = $stockResource;
        parent::__construct($context, $data);
    }

    public function toHtml()
    {
        if ($this->_request->getParam('isAjax', false)) {
            return '';
        }

        return parent::toHtml();
    }

    /**
     * @return int
     */
    public function isSimple()
    {
        $product = $this->registry->registry('current_product');
        $simple = 0;
        if ($product->getTypeId() == Type::TYPE_SIMPLE) {
            $simple = 1;
        }
        return $simple;
    }

    /**
     * @return int
     */
    public function isEnabled()
    {
        return (int)$this->system->isMultiEnabled();
    }

    /**
     * @return bool
     */
    public function isHaveAssigned()
    {
        $product = $this->registry->registry('current_product');
        return $this->stockResource->getTotalSku(null, $product->getId()) > 1;
    }
}
