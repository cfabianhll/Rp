<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column;

use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class CustomerGroup
 */
class CustomerGroup extends AbstractColumn
{

    private $collectionFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ResourceConnection $resource,
        WarehouseFactory $factory,
        WarehouseRepositoryInterface $repository,
        EncoderInterface $jsonEncoder,
        System $helper,
        Item $warehouseStockResource,
        CollectionFactory $collectionFactory,
        array $components = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $resource,
            $factory,
            $repository,
            $jsonEncoder,
            $helper,
            $warehouseStockResource,
            $components,
            $data
        );
    }

    /**
     * Get data
     *
     * @param array $item
     *
     * @return string
     * @throws NoSuchEntityException
     */
    protected function prepareItem(array $item)
    {
        $content = '';
        $origGroups = [];
        $collection = $this->repository->getById($item['warehouse_id'])->getCustomerGroups();

        if (count($collection)) {
            $origGroups = [];

            foreach ($collection as $group) {
                $origGroups[] = $group->getCode();
            }
        }

        if (!count($origGroups)) {
            return __('Not groups');
        }

        if (count($origGroups) == $this->collectionFactory->create()->getSize()) {
            return __('All Customer Groups');
        }

        foreach ($origGroups as $code) {
            // @codingStandardsIgnoreLine
            $content .= $code . '<br/>';
        }

        return $content;
    }
}
