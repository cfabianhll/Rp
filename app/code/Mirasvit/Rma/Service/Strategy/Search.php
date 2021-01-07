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


namespace Mirasvit\Rma\Service\Strategy;

use Magento\Framework\Api\SortOrder;
use Mirasvit\Rma\Api\Service\Strategy\SearchInterface;
use Mirasvit\Rma\Model\ResourceModel\Rma\CollectionFactory;

class Search implements SearchInterface
{
    /**
     * @var CollectionFactory
     */
    private $rmaCollectionFactory;

    /**
     * Search constructor.
     * @param CollectionFactory $rmaCollectionFactory
     */
    public function __construct(
        CollectionFactory $rmaCollectionFactory
    ) {
        $this->rmaCollectionFactory = $rmaCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaList($customerId, $order = null)
    {
        if (!$customerId && ! $order) {
            return [];
        }

        $collection = $this->rmaCollectionFactory->create();
        if ($order) {
            $collection->addOrderFilter($order->getId());
        }
        if ($customerId) {
            $collection->addFieldToFilter('main_table.customer_id', $customerId);
        }
        $collection->getSelect()->order(new \Zend_Db_Expr('rma_id '.SortOrder::SORT_DESC));

        return $collection->getItems();
    }
}

