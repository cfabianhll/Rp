<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\Source\Adminhtml;

use Magento\InventoryApi\Api\SourceRepositoryInterface;

/**
 * Source model
 */
class Source implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var SourceRepositoryInterface
     */
    protected $sourceRepository;

    /**
     * Source constructor.
     * @param SourceRepositoryInterface $sourceRepository
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository
    ) {
        $this->sourceRepository = $sourceRepository;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $collection = $this->sourceRepository->getList();
        $options = [];
        $items = $collection->getItems();
        $options[] = ['value' => '', 'label' => __('-- Please Select --')];
        if (count($items)) {
            /** @var \Magento\InventoryApi\Api\Data\SourceInterface $source */
            foreach ($items as $source) {
                $label = $source->getName();
                $options[] = ['value' => $source->getSourceCode(), 'label' => $label];
            }
        }
        return $options;
    }
}
