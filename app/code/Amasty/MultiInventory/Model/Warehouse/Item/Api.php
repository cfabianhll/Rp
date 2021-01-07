<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse\Item;

use Amasty\MultiInventory\Api\Data\WarehouseItemApiInterface;
use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;

/**
 * Class Api
 */
class Api extends DataObject implements WarehouseItemApiInterface
{
    /**
     * @var WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        WarehouseRepositoryInterface $repository,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        parent::__construct($data);
        $this->repository = $repository;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getQty()
    {
        return $this->getData(self::QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableQty()
    {
        return $this->getData(self::AVAILABLE_QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function getShipQty()
    {
        return $this->getData(self::SHIP_QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoomShelf()
    {
        return $this->getData(self::ROOM_SHELF);
    }

    /**
     * {@inheritdoc}
     */
    public function setSku($sku)
    {
        $this->setData(self::SKU, $sku);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->setData(self::CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function setQty($qty)
    {
        $this->setData(self::QTY, $qty);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAvailableQty($qty)
    {
        $this->setData(self::AVAILABLE_QTY, $qty);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setShipQty($qty)
    {
        $this->setData(self::SHIP_QTY, $qty);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRoomShelf($text)
    {
        $this->setData(self::ROOM_SHELF, $text);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemData()
    {
        $data = $this->getData();
        $newData = [
            'warehouse_id' => $this->repository->getByCode($data['code'])->getId(),
            'product_id' => $this->productRepository->get($data['sku'])->getId(),
            'qty' => $data['qty']
        ];

        if (isset($data[self::SHIP_QTY])) {
            $newData[self::SHIP_QTY] = $data[self::SHIP_QTY];
        }

        if (isset($data[self::AVAILABLE_QTY])) {
            $newData[self::AVAILABLE_QTY] = $data[self::AVAILABLE_QTY];
        }

        if (isset($data[self::ROOM_SHELF])) {
            $newData[self::ROOM_SHELF] = $data[self::ROOM_SHELF];
        }

        return $newData;
    }
}
