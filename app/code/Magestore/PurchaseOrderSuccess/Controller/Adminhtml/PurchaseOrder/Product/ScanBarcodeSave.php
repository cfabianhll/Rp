<?php
/**
 * Copyright Â© 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\PurchaseOrderSuccess\Controller\Adminhtml\PurchaseOrder\Product;

use Magestore\PurchaseOrderSuccess\Block\Adminhtml\PurchaseOrder\Edit\Fieldset\PurchaseSumary\Total;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magestore\PurchaseOrderSuccess\Model\System\Config\ProductSource;

/**
 * Class Save
 *
 * Used for save product list when scan barcode
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ScanBarcodeSave extends \Magestore\PurchaseOrderSuccess\Controller\Adminhtml\AbstractAction implements
    HttpPostActionInterface
{
    const BLOCK_GRID = Total::class;
    const BLOCK_GRID_NAME = 'purchaseorder.total';

    /**
     * @var \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\ItemService
     */
    protected $itemService;

    /**
     * @var \Magestore\SupplierSuccess\Service\Supplier\ProductService
     */
    protected $supplierProductService;

    /**
     * @var \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\PurchaseOrderService
     */
    protected $purchaseOrderService;

    /**
     * @var \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\ProductService
     */
    protected $productService;

    /**
     * @var \Magestore\PurchaseOrderSuccess\Api\PurchaseOrderRepositoryInterface
     */
    protected $purchaseOrderRepository;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var int
     */
    protected $productSource;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * ScanBarcodeSave constructor.
     *
     * @param \Magestore\PurchaseOrderSuccess\Controller\Adminhtml\Context $context
     * @param \Magestore\PurchaseOrderSuccess\Api\PurchaseOrderRepositoryInterface $purchaseOrderRepository
     * @param \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\PurchaseOrderService $purchaseOrderService
     * @param \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\ProductService $productService
     * @param \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\ItemService $itemService
     * @param \Magestore\SupplierSuccess\Service\Supplier\ProductService $supplierProductService
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magestore\PurchaseOrderSuccess\Controller\Adminhtml\Context $context,
        \Magestore\PurchaseOrderSuccess\Api\PurchaseOrderRepositoryInterface $purchaseOrderRepository,
        \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\PurchaseOrderService $purchaseOrderService,
        \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\ProductService $productService,
        \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\ItemService $itemService,
        \Magestore\SupplierSuccess\Service\Supplier\ProductService $supplierProductService,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->purchaseOrderRepository = $purchaseOrderRepository;
        $this->purchaseOrderService = $purchaseOrderService;
        $this->productService = $productService;
        $this->itemService = $itemService;
        $this->supplierProductService = $supplierProductService;
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->jsonFactory = $jsonFactory;
        $this->productSource = $context->getProductSourceConfig();
    }

    /**
     * Save product to purchase order
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json
     * |\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['barcode'])) {
            $barcode = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(\Magestore\BarcodeSuccess\Model\Barcode::class)
                ->load($params['barcode'], 'barcode');
            if ($barcode->getId()) {
                $productIds = [
                    $barcode->getProductId()
                ];
                $params['suggest_qty'] = [
                    [
                        'id' => $barcode->getProductId(),
                        'qty' => 0
                    ]
                ];

                if ($this->productSource == ProductSource::TYPE_SUPPLIER) {
                    $suppplierProductCollection = $this->supplierProductService
                        ->getProductsBySupplierId($params['supplier_id'], $productIds);
                    if ($suppplierProductCollection->getSize()) {
                        $this->itemService->addProductToPurchaseOrder(
                            $params['purchase_id'],
                            $suppplierProductCollection->getData(),
                            $params
                        );
                    } else {
                        return $this->reloadTotal($params, false);
                    }
                } else {
                    $productData = $this->productService->prepareProductForPO($productIds);
                    $this->itemService->addProductToPurchaseOrder($params['purchase_id'], $productData, $params);
                }

                $id = $params['purchase_id'];
                $purchaseOrder = $this->purchaseOrderRepository->get($id);
                $this->purchaseOrderService->updatePurchaseTotal($purchaseOrder);
                return $this->reloadTotal($params, true);
            } else {
                return $this->reloadTotal($params, false);
            }
        } else {
            return $this->reloadTotal($params, false);
        }
    }

    /**
     * Reload total
     *
     * @param array $params
     * @param boolean $result
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function reloadTotal($params, $result)
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultRaw */
        $jsonFactory = $this->jsonFactory->create();
        $id = $params['purchase_id'];
        $purchaseOrder = $this->purchaseOrderRepository->get($id);
        $this->purchaseOrderService->updatePurchaseTotal($purchaseOrder);
        $this->_registry->register('current_purchase_order', $purchaseOrder);
        return $jsonFactory->setData(
            [
                'total_block' => $this->layoutFactory->create()->createBlock(
                    static::BLOCK_GRID,
                    static::BLOCK_GRID_NAME
                )->toHtml(),
                'result' => $result
            ]
        );
    }
}
