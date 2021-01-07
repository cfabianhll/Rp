<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model;

/**
 * Registry to store configurable product ids need to be reindexed
 * in Amasty\MultiInventory\Observer\UpdateInventoryObserver
 */
class ConfigurableProductIdsReindexRegistry
{
    private $idsToReindex = [];

    /**
     * @return array
     */
    public function getIdsToReindex()
    {
        return $this->idsToReindex;
    }

    /**
     * @param string|array $id
     */
    public function addIdsToReindex($ids)
    {
        if (is_array($ids)) {
            $this->idsToReindex = array_merge($this->idsToReindex, $ids);
        } else {
            $this->idsToReindex[] = $ids;
        }
    }
}
