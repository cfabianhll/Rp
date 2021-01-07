<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Report;

use Amasty\MultiInventory\Controller\Adminhtml\Report;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

/**
 * Class ExportLowstockCsv
 */
class ExportLowstockCsv extends Report
{
    /**
     * @return ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $fileName = 'amasty_warehouse_lowstock.csv';
        $exportBlock = $this->_view->getLayout()->getChildBlock(
            'adminhtml.block.amasty.warehouse.lowstock.grid',
            'grid.export'
        );

        return $this->_fileFactory->create(
            $fileName,
            $exportBlock->getCsvFile(),
            DirectoryList::VAR_DIR
        );
    }
}
