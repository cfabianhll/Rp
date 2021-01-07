<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Import;

use Amasty\MultiInventory\Controller\Adminhtml\AbstractImport;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassBackup
 */
class MassBackup extends AbstractImport
{
    /**
     * Execute action
     *
     * @return Redirect
     * @throws LocalizedException|\Exception
     */
    public function execute()
    {
        $this->backup();

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('amasty_multi_inventory/*/index');
    }

    /**
     * Create file Back up
     *
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function backup()
    {
        $filename = $this->timezone->date()->format('Y_m_d_H_i_s') . '.csv';
        $this->csv->init($filename, 'a');
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        foreach ($collection as $stock) {
            $this->csv->writeRow([$stock->getProduct()->getSku(), $stock->getWarehouse()->getCode(), $stock->getQty()]);
        }
        $this->csv->destruct();
        $path = $this->csv->getFilename();
        $this->messageManager
            ->addSuccessMessage(
                __('The back up has been created successfully. The exported file can be found here: %1', $path)
            );

        parent::import();

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('amasty_multi_inventory/*/index');
    }
}
