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

class Store extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     */
    private $storeCollectionFactory;
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;

    /**
     * Store constructor.
     * @param \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->storeCollectionFactory = $storeCollectionFactory;
        $this->context                = $context;

        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getCoreStoreOptionArray()
    {
        $result = [];
        $arr = $this->storeCollectionFactory->create()->toArray();
        foreach ($arr['items'] as $value) {
            $result[$value['store_id']] = $value['name'];
        }

        return $result;
    }
}