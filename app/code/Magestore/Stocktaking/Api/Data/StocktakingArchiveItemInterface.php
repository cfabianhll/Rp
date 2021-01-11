<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Api\Data;

/**
 * Stocktaking - StocktakingArchiveItemInterface
 */
interface StocktakingArchiveItemInterface
{
    /**
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const STOCKTAKING_ID = 'stocktaking_id';
    const PRODUCT_ID = 'product_id';
    const PRODUCT_NAME = 'product_name';
    const PRODUCT_SKU = 'product_sku';
    const QTY_IN_SOURCE = 'qty_in_source';
    const COUNTED_QTY = 'counted_qty';
    const DIFFERENCE_REASON = 'difference_reason';
    
    /**
     * Get Id
     *
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * Get Stocktaking Id
     *
     * @return int|null
     */
    public function getStocktakingId(): ?int;

    /**
     * Get Product Id
     *
     * @return int|null
     */
    public function getProductId(): ?int;

    /**
     * Get Product Name
     *
     * @return string|null
     */
    public function getProductName(): ?string;

    /**
     * Get Product Sku
     *
     * @return string|null
     */
    public function getProductSku(): ?string;

    /**
     * Get Qty In Source
     *
     * @return float|null
     */
    public function getQtyInSource(): ?float;

    /**
     * Get Counted Qty
     *
     * @return float|null
     */
    public function getCountedQty(): ?float;

    /**
     * Get Difference Reason
     *
     * @return string|null
     */
    public function getDifferenceReason(): ?string;

    /**
     * Set Id
     *
     * @param int|null $id
     * @return $this
     */
    public function setId($id);

    /**
     * Set Stocktaking Id
     *
     * @param int|null $stocktakingId
     * @return $this
     */
    public function setStocktakingId(?int $stocktakingId);

    /**
     * Set Product Id
     *
     * @param int|null $productId
     * @return $this
     */
    public function setProductId(?int $productId);

    /**
     * Set Product Name
     *
     * @param string|null $productName
     * @return $this
     */
    public function setProductName(?string $productName);

    /**
     * Set Product Sku
     *
     * @param string|null $productSku
     * @return $this
     */
    public function setProductSku(?string $productSku);

    /**
     * Set Qty In Source
     *
     * @param float|null $qtyInSource
     * @return $this
     */
    public function setQtyInSource(?float $qtyInSource);

    /**
     * Set Counted Qty
     *
     * @param float|null $countedQty
     * @return $this
     */
    public function setCountedQty(?float $countedQty);

    /**
     * Set Difference Reason
     *
     * @param string|null $differenceReason
     * @return $this
     */
    public function setDifferenceReason(?string $differenceReason);
}
