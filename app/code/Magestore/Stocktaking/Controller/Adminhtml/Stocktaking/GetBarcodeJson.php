<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types = 1);

namespace Magestore\Stocktaking\Controller\Adminhtml\Stocktaking;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magestore\Stocktaking\Api\Data\StocktakingItemInterface;
use Magestore\Stocktaking\Api\StocktakingItemRepositoryInterface;
use Magestore\Stocktaking\Api\StocktakingManagementInterface;

/**
 * Get list barcode data
 */
class GetBarcodeJson extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * @var StocktakingManagementInterface
     */
    protected $stocktakingManagement;
    /**
     * @var StocktakingItemRepositoryInterface
     */
    protected $stocktakingItemRepository;

    /**
     * GetBarcodeJson constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param StocktakingManagementInterface $stocktakingManagement
     * @param StocktakingItemRepositoryInterface $stocktakingItemRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        StocktakingManagementInterface $stocktakingManagement,
        StocktakingItemRepositoryInterface $stocktakingItemRepository
    ) {
        parent::__construct($context);
        $this->stocktakingManagement = $stocktakingManagement;
        $this->stocktakingItemRepository = $stocktakingItemRepository;
    }

    /**
     * History action.
     *
     * @return \Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->getResponse()->representJson(
            $this->stocktakingManagement->getSelectBarcodeProductListJson($this->getRestrictedProductIds())
        );
    }

    /**
     * Get Restricted Product Ids
     *
     * @return array
     */
    public function getRestrictedProductIds()
    {
        $stocktakingId = $this->getRequest()->getParam('stocktaking_id');
        $itemCollection = $this->stocktakingItemRepository->getListByStocktakingId((int)$stocktakingId);
        if ($itemCollection->getSize()) {
            return $itemCollection->getColumnValues(StocktakingItemInterface::PRODUCT_ID);
        } else {
            return [];
        }
    }
}
