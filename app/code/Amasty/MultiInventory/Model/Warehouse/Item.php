<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseItemInterface;
use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\AbstractWarehouse;
use Amasty\MultiInventory\Model\Config\Source\Backorders;
use Amasty\MultiInventory\Model\Config\Source\BackordersDefault;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Item
 */
class Item extends AbstractWarehouse implements WarehouseItemInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var System
     */
    private $system;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item::class);
    }

    /**
     * Item constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param WarehouseFactory $warehouseFactory
     * @param ProductRepositoryInterface $productRepository
     * @param System $system
     * @param StockRegistryInterface $stockRegistry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        WarehouseFactory $warehouseFactory,
        ProductRepositoryInterface $productRepository,
        System $system,
        StockRegistryInterface $stockRegistry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->warehouseFactory = $warehouseFactory;
        $this->productRepository = $productRepository;
        $this->system = $system;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Recalculate for Available Qty
     *
     * @return Item
     * @throws NoSuchEntityException
     */
    public function recalcAvailable()
    {
        if ($this->system->getAvailableDecreese()) {
            $this->setAvailableQty($this->getQty() - $this->getShipQty());
        } else {
            $this->setAvailableQty($this->getQty());
        }

        if ($this->_registry->registry('am_dont_recalc_availability')
            || $this->getProduct()->getTypeId() != Type::TYPE_SIMPLE
        ) {
            return $this;
        }

        if ($this->getRealQty() <= 0 && !$this->isCanBackorder()) {
            $this->setStockStatus(Stock::STOCK_OUT_OF_STOCK);
        } else {
            $this->setStockStatus(Stock::STOCK_IN_STOCK);
        }

        return $this;
    }

    /**
     * @return float
     */
    public function getRealQty()
    {
        $qty = $this->getQty();
        $minQty = $this->stockRegistry->getStockItem($this->getProductId())->getMinQty();

        if ($this->system->getAvailableDecreese()) {
            $qty = $this->getAvailableQty();
        }

        return $qty - $minQty;
    }

    /**
     * @return bool
     */
    public function isCanBackorder()
    {
        return $this->getBackordersValue() > 0;
    }

    /**
     * Is can show the backorder notice
     *
     * @return bool
     */
    public function isShowBackorderNotice()
    {
        return $this->getBackordersValue() == Stock::BACKORDERS_YES_NOTIFY;
    }

    /**
     * @param int $productId
     *
     * @return array
     */
    public function getItems($productId)
    {
        return $this->getResource()->getItems($productId);
    }

    /**
     * @return Product
     * @throws NoSuchEntityException
     */
    public function getProduct()
    {
        if ($this->product == null) {
            $this->product = $this->productRepository->getById($this->getProductId());
        }

        return $this->product;
    }

    /**
     * @return bool
     */
    public function isStockStatusChanged()
    {
        if ($this->getOrigData('stock_status') != $this->getData('stock_status')) {
            return true;
        }

        if ($this->getData('available_qty') <= 0
            && $this->getOrigData('available_qty') > 0
        ) {
            return true;
        }

        if ($this->getData('available_qty') > 0
            && $this->getOrigData('available_qty') <= 0
        ) {
            return true;
        }

        return false;
    }

    /**
     * Warehouse item (warehouse product stock) have backorder configuration
     * which extend the Warehouse backorders configuration or product backorders configuration,
     * depends by system configuration
     *
     * @return int
     */
    public function getBackordersValue()
    {
        $bockorderValue = $this->getBackorders();

        if ($bockorderValue == Backorders::USE_CONFIG_OPTION_VALUE) {
            if ($this->system->getBackordersUseDefault() == BackordersDefault::USE_PRODUCT_BACKORDERS) {
                $bockorderValue = $this->stockRegistry->getStockItem($this->getProductId())->getBackorders();
            } else {
                $bockorderValue = $this->getWarehouse()->getBackorders();
            }
        }

        return $bockorderValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getQty()
    {
        return (float)$this->getData(self::QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableQty()
    {
        return (float)$this->getData(self::AVAILABLE_QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function getShipQty()
    {
        return (float)$this->getData(self::SHIP_QTY);
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
    public function setId($id)
    {
        $this->setData(self::ITEM_ID, $id);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($productWarehouse)
    {
        $this->setData(self::PRODUCT_ID, $productWarehouse);

        return $this;
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
    public function getStockStatus()
    {
        return $this->_getData(self::STOCK_STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStockStatus($stockStatus)
    {
        $this->setData(self::STOCK_STATUS, $stockStatus);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBackorders()
    {
        return $this->_getData(self::BACKORDERS);
    }

    /**
     * {@inheritdoc}
     */
    public function setBackorders($backorders)
    {
        $this->setData(self::BACKORDERS, $backorders);

        return $this;
    }
}
