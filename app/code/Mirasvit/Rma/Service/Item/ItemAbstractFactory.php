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

use Mirasvit\Rma\Model\ItemFactory;
use Mirasvit\Rma\Model\OfflineItemFactory;
use Mirasvit\Rma\Model\Item;
use Mirasvit\Rma\Model\OfflineItem;

class ItemAbstractFactory
{
    /**
     * @var OfflineItemFactory
     */
    private $offlineItemFactory;
    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * ItemAbstractFactory constructor.
     * @param ItemFactory $itemFactory
     * @param OfflineItemFactory $offlineItemFactory
     */
    public function __construct(
        ItemFactory $itemFactory,
        OfflineItemFactory $offlineItemFactory
    ) {
        $this->itemFactory = $itemFactory;
        $this->offlineItemFactory = $offlineItemFactory;
    }

    /**
     * @param array $data
     * @return Item|OfflineItem
     */
    public function get($data)
    {
        if (isset($data['is_offline'])) {
            $item = $this->offlineItemFactory->create();
        } else {
            $item = $this->itemFactory->create();
        }

        return $item;
    }
}