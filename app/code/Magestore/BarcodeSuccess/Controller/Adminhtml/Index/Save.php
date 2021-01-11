<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\BarcodeSuccess\Controller\Adminhtml\Index;

use Magestore\BarcodeSuccess\Model\History;
use Magestore\BarcodeSuccess\Model\Source\GenerateType;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\ValidatorException;

/**
 * Class Generate
 */
class Save extends \Magestore\BarcodeSuccess\Controller\Adminhtml\AbstractIndex implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * Save constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magestore\BarcodeSuccess\Helper\Data $data
     * @param \Magestore\BarcodeSuccess\Model\Locator\LocatorInterface $locator
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magestore\BarcodeSuccess\Helper\Data $data,
        \Magestore\BarcodeSuccess\Model\Locator\LocatorInterface $locator,
        \Magento\Framework\DB\TransactionFactory $transactionFactory
    ) {
        parent::__construct($context, $resultPageFactory, $data, $locator);
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * Execute action
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $path = '*/*/';
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $params = $this->getRequest()->getParams();
            $one_barcode_per_sku = $this->helper->getStoreConfig('barcodesuccess/general/one_barcode_per_sku');
            if ($one_barcode_per_sku) {
                $params['general_information']['generate_type'] = GenerateType::ITEM;
            }
            if (isset($params['links']) && is_string($params['links'])) {
                $params['links'] = json_decode($params['links'], true);
            }
            if (isset($params['links']['selected_products'])) {
                $reason = '';
                $barcodes = [];
                $totalQty = 0;
                foreach ($params['links']['selected_products'] as $productData) {
                    $data = [];
                    $data['product_id'] = $productData['id'];
                    $data['qty'] = (isset($productData['purchased_time']))?$productData['qty']:1;
                    $data['product_sku'] = $productData['sku'];
                    $data['supplier_code'] = isset($productData['supplier'])?$productData['supplier']:'';
                    if (isset($productData['purchased_time'])) {
                        $data['purchased_time'] = $productData['purchased_time'];
                    }
                    $totalQty += floatval($data['qty']);
                    $barcodes[] = $data;
                }
                if (isset($params['general_information']['reason'])) {
                    $reason = $params['general_information']['reason'];
                }
                if (isset($params['general_information']['generate_type'])) {
                    $result = [];
                    $historyId = $this->saveHistory($totalQty, History::GENERATED, $reason);
                    switch ($params['general_information']['generate_type']) {
                        case GenerateType::ITEM:
                            $result = $this->generateTypeItem($barcodes, $historyId);
                            break;
                        case GenerateType::PURCHASE:
                            $result = $this->generateTypePurchase($barcodes, $historyId);
                            break;
                    }
                    $path = '*/history/view/id/'.$historyId;

                    if (count($result) > 0) {
                        if (isset($result['success'])) {
                            $this->messageManager->addSuccessMessage(
                                __("%1 barcode(s) has been generated.", count($result['success']))
                            );
                        } else {
                            $this->removeHistory($historyId);
                            $path = '*/*/generate';
                        }
                        if (isset($result['fail'])) {
                            $this->messageManager->addErrorMessage(
                                __(
                                    'Cannot generate %1 barcode(s), please change Barcode Pattern from the '
                                    . 'configuration to increase the maximum barcode number',
                                    count($result['fail'])
                                )
                            );
                        }
                    }
                }
            } else {
                $path = '*/*/generate';
                $this->messageManager->addErrorMessage(__('You must select the product to generate the barcode'));
            }
        } catch (\Exception $ex) {
            if ($ex instanceof ValidatorException) {
                $path = '*/*/generate';
            }
            $this->messageManager->addErrorMessage($ex->getMessage());
        }
        return $resultRedirect->setPath($path);
    }

    /**
     * Generate type item function
     *
     * @param array $barcodes
     * @param int $historyId
     * @throws ValidatorException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function generateTypeItem($barcodes, $historyId)
    {
        $result = [];
        $unsaved = [];
        $saveTransaction = $this->transactionFactory->create();

        if (!$this->validateItems($barcodes)) {
            throw new ValidatorException(
                __("You must assign quantity for all selected items.")
            );
        }

        foreach ($barcodes as $key => $data) {
            if ($data['qty'] > 1) {
                $maxQty = floatval($data['qty']);
                $data['qty'] = 1;
                for ($i = 0; $i < $maxQty; $i++) {
                    $data['history_id'] = $historyId;
                    $data['barcode'] = $this->generateBarcode();
                    if ($data['barcode'] == false) {
                        $result['fail'][] = $data['product_sku'];
                    } else {
                        $result['success'][] = $data['product_sku'];
                        $unsaved[] = $data['barcode'];
                        $barcode = $this->helper
                            ->getModel(\Magestore\BarcodeSuccess\Api\Data\BarcodeInterface::class);
                        $barcode->setData($data);
                        $saveTransaction->addObject($barcode);
                    }
                }
            } else {
                $data['history_id'] = $historyId;
                $data['barcode'] = $this->generateBarcode();
                if ($data['barcode'] == false) {
                    $result['fail'][] = $data['product_sku'];
                } else {
                    $result['success'][] = $data['product_sku'];
                    $unsaved[] = $data['barcode'] ;
                    $barcode = $this->helper->getModel(\Magestore\BarcodeSuccess\Api\Data\BarcodeInterface::class);
                    $barcode->setData($data);
                    $saveTransaction->addObject($barcode);
                }
            }
        }
        if (count($unsaved) > 0) {
            $saveTransaction->save();
            $this->locator->remove('generated_barcodes');
        }
        return $result;
    }

    /**
     * Validate items for empty qty
     *
     * @param array $items
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function validateItems($items)
    {
        foreach ($items as $key => $data) {
            if (empty($data['qty'])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Generate type purchase function
     *
     * @param array $barcodes
     * @param int $historyId
     */
    public function generateTypePurchase($barcodes, $historyId)
    {
        $result = [];
        $unsaved = [];
        $saveTransaction = $this->transactionFactory->create();
        foreach ($barcodes as $data) {
            $data['history_id'] = $historyId;
            $data['barcode'] = $this->generateBarcode();
            if ($data['barcode'] == false) {
                $result['fail'][] = $data['product_sku'];
            } else {
                $result['success'][] = $data['product_sku'];
                $unsaved[] = $data['barcode'] ;
                $barcode = $this->helper->getModel(\Magestore\BarcodeSuccess\Api\Data\BarcodeInterface::class);
                $barcode->setData($data);
                $saveTransaction->addObject($barcode);
            }
        }
        if (count($unsaved) > 0) {
            $saveTransaction->save();
            $this->locator->remove('generated_barcodes');
        }
        return $result;
    }

    /**
     * Generate barcode function
     *
     * @return mixed
     */
    public function generateBarcode()
    {
        $code = $this->helper->generateBarcode();
        return $code;
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
}
