<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Export;

use Amasty\MultiInventory\Block\Adminhtml\Export\Filter;
use Amasty\MultiInventory\Model\Export\Export as WarehouseExport;
use Magento\AdvancedPricingImportExport\Controller\Adminhtml\Export\GetFilter as AdvancedPricingImportExportGetFilter;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\View\Result\Layout;
use Magento\ImportExport\Controller\Adminhtml\Export\GetFilter as ImportExportGetFilter;
use Magento\ImportExport\Model\Export as ModelExport;

/**
 * Class GetFilter
 */
class GetFilter
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var Export
     */
    private $export;

    /**
     * GetFilter constructor.
     *
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param MessageManager $messageManager
     * @param ModelExport $export
     */
    public function __construct(
        RequestInterface $request,
        ResultFactory $resultFactory,
        MessageManager $messageManager,
        ModelExport $export
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
        $this->export = $export;
    }

    /**
     * Subject can be overridden by \Magento\AdvancedPricingImportExport\Controller\Adminhtml\Export\GetFilter
     * Around plugin to change block for attribute list
     *
     * @param ImportExportGetFilter | AdvancedPricingImportExportGetFilter $subject
     * @param \Closure $proceed
     *
     * @return Redirect|ResultInterface|Layout|mixed
     */
    public function aroundExecute($subject, \Closure $proceed)
    {
        $data = $this->request->getParams();

        if ($data['entity'] !== WarehouseExport::MW_EXPORT_ENTITY) {
            return $proceed();
        }

        if ($this->request->isXmlHttpRequest() && $data) {
            $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);

            /** @var $resultLayout Layout */
            $resultLayout->getDefaultLayoutHandle();

            /** @var $attrFilterBlock \Amasty\Faq\Block\Adminhtml\Export\Filter */
            $resultLayout->getLayout()->addBlock(
                Filter::class,
                'wh.export',
                'root'
            );
            $this->export->setData($data);

            return $resultLayout;
        } else {
            $this->messageManager->addError(__('Please correct the data sent.'));
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('adminhtml/*/index');

        return $resultRedirect;
    }
}
