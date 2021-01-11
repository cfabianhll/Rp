<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\BarcodeSuccess\Controller\Adminhtml\Index;

use Magestore\BarcodeSuccess\Model\History;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class MassGenerate
 *
 * Used to mass generate
 */
class MassGenerate extends \Magestore\BarcodeSuccess\Controller\Adminhtml\Index\Save implements
    HttpPostActionInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * @var Filter
     */
    protected $filter;
    
    /**
     * MassGenerate constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magestore\BarcodeSuccess\Helper\Data $data
     * @param \Magestore\BarcodeSuccess\Model\Locator\LocatorInterface $locator
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magestore\BarcodeSuccess\Helper\Data $data,
        \Magestore\BarcodeSuccess\Model\Locator\LocatorInterface $locator,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        CollectionFactory $collectionFactory,
        Filter $filter
    ) {
        parent::__construct($context, $resultPageFactory, $data, $locator, $transactionFactory);
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
    }
    
    /**
     * Execute
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $path = 'catalog/product/';
        $resultRedirect = $this->resultRedirectFactory->create();
        $source = $this->getRequest()->getParam('source');
        $selected = $this->getSelectedProduct();
        if (empty($selected)) {
            return $resultRedirect->setPath($path);
        }
        if (isset($source) && $source == 'product_listing') {
            try {
                $barcodes = [];
                $totalQty = 1;
                foreach ($selected as $productId) {
                    $productModel = $this->_objectManager->create(\Magento\Catalog\Model\Product::class)
                        ->load($productId);
                    if ($productModel->getId()) {
                        $barcodes[] = [
                            'product_id' => $productId,
                            'qty' => 1,
                            'product_sku' => $productModel->getData('sku'),
                            'supplier_code' => ''
                        ];
                    }
                    $totalQty++;
                }

                $historyId = $this->saveHistory($totalQty, History::GENERATED, '');
                $result = $this->generateTypeItem($barcodes, $historyId);
                if (count($result) > 0) {
                    if (isset($result['success'])) {
                        $this->messageManager->addSuccessMessage(
                            __("%1 barcode(s) has been generated.", count($result['success']))
                        );
                    } else {
                        $this->removeHistory($historyId);
                        $path = 'catalog/product/';
                    }
                    if (isset($result['fail'])) {
                        $this->messageManager->addErrorMessage(
                            __(
                                'Cannot generate %1 barcode(s), please change Barcode Pattern from the configuration'
                                .' to increase the maximum barcode number',
                                count($result['fail'])
                            )
                        );
                    }
                }

            } catch (\Exception $ex) {
                $this->messageManager->addErrorMessage($ex->getMessage());
            }
        }
        return $resultRedirect->setPath($path);
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_BarcodeSuccess::generate_barcode');
    }
    
    /**
     * Get selected product
     *
     * @return array
     */
    protected function getSelectedProduct()
    {
        $selected = $this->getRequest()->getParam(Filter::SELECTED_PARAM);
        $excluded = $this->getRequest()->getParam(Filter::EXCLUDED_PARAM);
        if (empty($selected) && !empty($excluded)) {
            try {
                $collection = $this->filter->getCollection($this->collectionFactory->create());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Could not create products collection.'));
                return [];
            }
            if (is_array($excluded)) {
                $collection->addFieldToFilter('entity_id', ['nin' => $excluded]);
            }
            $selected = $collection->getColumnValues('entity_id');
        }
        return $selected;
    }
}
