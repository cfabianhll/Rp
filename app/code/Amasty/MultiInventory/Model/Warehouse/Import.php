<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseImportInterface;
use Amasty\MultiInventory\Model\AbstractWarehouse;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Import
 */
class Import extends AbstractWarehouse implements WarehouseImportInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Product
     */
    private $product;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Amasty\MultiInventory\Model\ResourceModel\Warehouse\Import::class);
    }

    /**
     * Import constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param WarehouseFactory $warehouseFactory
     * @param ProductRepositoryInterface $productRepository
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
    public function getProduct()
    {
        if ($this->product == null) {
            $this->product = $this->productRepository->getById($this->getProductId());
        }

        return $this->product;
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
    public function getNewQty()
    {
        return $this->getData(self::NEW_QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function getImportNumber()
    {
        return $this->getData(self::IMPORT_NUMBER);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($id)
    {
        $this->setData(self::PRODUCT_ID, $id);

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
    public function setNewQty($qty)
    {
        $this->setData(self::NEW_QTY, $qty);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setImportNumber($number)
    {
        $this->setData(self::IMPORT_NUMBER, $number);

        return $this;
    }
}
