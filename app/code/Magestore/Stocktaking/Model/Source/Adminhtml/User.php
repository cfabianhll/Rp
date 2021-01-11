<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\Source\Adminhtml;

use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;

/**
 * User model
 */
class User implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var UserCollectionFactory
     */
    protected $userCollectionFactory;

    /**
     * User constructor.
     * @param UserCollectionFactory $userCollectionFactory
     */
    public function __construct(
        UserCollectionFactory $userCollectionFactory
    ) {
        $this->userCollectionFactory = $userCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $collection = $this->userCollectionFactory->create();
        $options = [];
        $items = $collection->getItems();
        $options[] = ['value' => '', 'label' => __('-- Please Select --')];
        if (count($items)) {
            /** @var \Magento\User\Api\Data\UserInterface $user */
            foreach ($items as $user) {
                $options[] = ['value' => $user->getId(), 'label' => $user->getName()];
            }
        }
        return $options;
    }
}
