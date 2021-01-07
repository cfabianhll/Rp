<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Indexer\Warehouse\Action;

use Amasty\MultiInventory\Model\Indexer\Warehouse\AbstractAction;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Rows
 */
class Rows extends AbstractAction
{
    /**
     * @param array|int $ids
     * @throws LocalizedException
     */
    public function execute($ids)
    {
        if (empty($ids)) {
            throw new LocalizedException(
                __('Could not rebuild index for empty products array')
            );
        }

        try {
            $this->reindexRows($ids);
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
