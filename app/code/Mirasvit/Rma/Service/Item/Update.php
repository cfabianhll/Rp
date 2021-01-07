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


namespace Mirasvit\Rma\Service\Item;

/**
 * Update RMA items
 */
class Update
{
    /**
     * @var ItemAbstractFactory
     */
    private $itemAbstractFactoryFactory;

    /**
     * @param ItemAbstractFactory $itemAbstractFactoryFactory
     */
    public function __construct(
        ItemAbstractFactory $itemAbstractFactoryFactory
    ) {
        $this->itemAbstractFactoryFactory = $itemAbstractFactoryFactory;
    }

    /**
     * @param \Mirasvit\Rma\Model\Rma $rma
     * @param array $items
     * @return void
     */
    public function updateItems($rma, $items)
    {
        foreach ($items as $item) {
            $rmaItem = $this->itemAbstractFactoryFactory->get($item);
            if (isset($item['item_id']) && $item['item_id']) {
                $rmaItem->load((int) $item['item_id']);
            }
            unset($item['item_id']);
            $rmaItem->addData($item)
                ->setRmaId($rma->getId());

            $rmaItem->save();
        }
    }
}