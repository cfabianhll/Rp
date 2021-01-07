<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Import;

use Amasty\MultiInventory\Controller\Adminhtml\Import;
use Amasty\MultiInventory\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 */
class Index extends Import
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Index constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Data $helper
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Data $helper,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->helper = $helper;
    }

    /**
     * Index action
     *
     * @return Page
     */
    public function execute()
    {
        $this->messageManager->addNoticeMessage(
            $this->helper->getMaxUploadSizeMessage()
        );
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_MultiInventory::import_stocks');
        $resultPage->addBreadcrumb(__('Inventory'), __('Inventory'));
        $resultPage->addBreadcrumb(__('Import'), __('Import'));
        $resultPage->getConfig()->getTitle()->prepend(__('Import Stock'));

        return $resultPage;
    }
}
