<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column\Stock\Source;

use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Collection;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Warehouse
 */
class Warehouse implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    private $factory;

    private $options;

    private $shortOptions;

    /**
     * Warehouse constructor.
     *
     * @param CollectionFactory $factory
     */
    public function __construct(
        CollectionFactory $factory
    ) {
        $this->factory = $factory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options == null) {
            /** @var  Collection $collection */
            $collection = $this->factory->create();
            $items = $collection
                ->addFieldToSelect('warehouse_id')
                ->addFieldToSelect('title')
                ->addFieldToFilter('manage', 1)
                ->toArray();

            foreach ($items['items'] as $item) {
                $this->options[] = ['value' => $item['warehouse_id'], 'label' => __($item['title'])];
            }
        }

        return $this->options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        if ($this->shortOptions == null) {
            $items = $this->factory->create()
                ->addFieldToSelect('warehouse_id')
                ->addFieldToSelect('title')
                ->addFieldToFilter('manage', 1)
                ->toArray();

            foreach ($items['items'] as $item) {
                $this->shortOptions[] = [$item['warehouse_id'] => __($item['title'])];
            }
        }

        return $this->shortOptions;
    }
}
