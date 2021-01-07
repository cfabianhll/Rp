<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Indexer\Warehouse\Action;

use Amasty\MultiInventory\Model\Indexer\Warehouse\AbstractAction;
use Exception;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Full
 */
class Full extends AbstractAction
{
    /**
     * @param null $ids
     * @throws LocalizedException
     */
    public function execute($ids = null)
    {
        try {
            $this->reindexAll();
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
