<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Warehouse;

use Amasty\MultiInventory\Controller\Adminhtml\Warehouse;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;

/**
 * Class Gridwh
 */
class Gridwh extends Warehouse
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $repository;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * Gridwh constructor.
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param LayoutFactory $layoutFactory
     * @param ProductRepositoryInterface $repository
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        LayoutFactory $layoutFactory,
        ProductRepositoryInterface $repository,
        Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->repository = $repository;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @return $this|Redirect
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('product_id', false);
        $product = null;

        if ($productId) {
            $product = $this->repository->getById($productId);
            $this->coreRegistry->register('current_product', $product);
        }

        if (!$product) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('warehouse/*/', ['_current' => true, 'id' => null]);
        }
        $resultRaw = $this->resultRawFactory->create();

        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                \Amasty\MultiInventory\Block\Adminhtml\Warehouse\Tab\Warehouse::class,
                'warehouse.grid'
            )->toHtml()
        );
    }
}
