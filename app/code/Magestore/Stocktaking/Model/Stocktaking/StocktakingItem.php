<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\Stocktaking;

use Magestore\Stocktaking\Api\Data\StocktakingItemInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\StocktakingItem as StocktakingItemResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Model StocktakingItem
 */
class StocktakingItem extends AbstractModel implements StocktakingItemInterface
{

    /**
     * StocktakingItem construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(StocktakingItemResource::class);
    }
    
    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        $id = $this->getData(self::ID);
        return $id ? (int) $id : null;
    }

    /**
     * @inheritdoc
     */
    public function getStocktakingId(): ?int
    {
        return $this->getData(self::STOCKTAKING_ID) ? (int) $this->getData(self::STOCKTAKING_ID) : null;
    }

    /**
     * @inheritdoc
     */
    public function getProductId(): ?int
    {
        return $this->getData(self::PRODUCT_ID) ? (int) $this->getData(self::PRODUCT_ID) : null;
    }

    /**
     * @inheritdoc
     */
    public function getProductName(): ?string
    {
        return $this->getData(self::PRODUCT_NAME);
    }

    /**
     * @inheritdoc
     */
    public function getProductSku(): ?string
    {
        return $this->getData(self::PRODUCT_SKU);
    }

    /**
     * @inheritdoc
     */
    public function getQtyInSource(): ?float
    {
        return $this->getData(self::QTY_IN_SOURCE) ? (float) $this->getData(self::QTY_IN_SOURCE) : null;
    }

    /**
     * @inheritdoc
     */
    public function getCountedQty(): ?float
    {
        return $this->getData(self::COUNTED_QTY) ? (float) $this->getData(self::COUNTED_QTY) : null;
    }

    /**
     * @inheritdoc
     */
    public function getDifferenceReason(): ?string
    {
        return $this->getData(self::DIFFERENCE_REASON);
    }
    
    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function setStocktakingId(?int $stocktakingId)
    {
        return $this->setData(self::STOCKTAKING_ID, $stocktakingId);
    }

    /**
     * @inheritdoc
     */
    public function setProductId(?int $productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @inheritdoc
     */
    public function setProductName(?string $productName)
    {
        return $this->setData(self::PRODUCT_NAME, $productName);
    }

    /**
     * @inheritdoc
     */
    public function setProductSku(?string $productSku)
    {
        return $this->setData(self::PRODUCT_SKU, $productSku);
    }

    /**
     * @inheritdoc
     */
    public function setQtyInSource(?float $qtyInSource)
    {
        return $this->setData(self::QTY_IN_SOURCE, $qtyInSource);
    }

    /**
     * @inheritdoc
     */
    public function setCountedQty(?float $countedQty)
    {
        return $this->setData(self::COUNTED_QTY, $countedQty);
    }

    /**
     * @inheritdoc
     */
    public function setDifferenceReason(?string $differenceReason)
    {
        return $this->setData(self::DIFFERENCE_REASON, $differenceReason);
    }
}
