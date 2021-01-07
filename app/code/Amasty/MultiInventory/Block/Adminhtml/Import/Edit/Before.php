<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Import\Edit;

use Amasty\MultiInventory\Helper\Data;
use Amasty\MultiInventory\Model\Warehouse\ImportFactory;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Json\EncoderInterface;

/**
 * Class Before
 */
class Before extends Template
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var WarehouseFactory
     */
    private $whFactory;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var ImportFactory
     */
    private $importFactory;

    /**
     * Before constructor.
     * @param Context $context
     * @param Data $helper
     * @param EncoderInterface $jsonEncoder
     * @param WarehouseFactory $whFactory
     * @param ProductFactory $productFactory
     * @param ImportFactory $importFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        EncoderInterface $jsonEncoder,
        WarehouseFactory $whFactory,
        ProductFactory $productFactory,
        ImportFactory $importFactory,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
        $this->whFactory = $whFactory;
        $this->productFactory = $productFactory;
        $this->importFactory = $importFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getUrlUploader()
    {
        return $this->getUrl('amasty_multi_inventory/*/uploader');
    }

    /**
     * @return float
     */
    public function getMaxSize()
    {
        return (float)$this->helper->getMaxSizeFile();
    }

    /**
     * @return string
     */
    public function listIdentifier()
    {
        return $this->jsonEncoder->encode([0 => 'sku', 1 => 'id']);
    }

    /**
     * @return string
     */
    public function listFileType()
    {
        return $this->jsonEncoder->encode([0 => 'csv', 1 => 'xml', 2 => 'xml']);
    }

    /**
     * @return string
     */
    public function listCodes()
    {
        return $this->jsonEncoder->encode($this->whFactory->create()->getWhCodes());
    }

    /**
     * @return string
     */
    public function listProducts()
    {
        $list = [];
        $ids = $this->productFactory->create()->getCollection()
            ->addFieldToFilter('type_id', Type::TYPE_SIMPLE)
            ->getAllIds();
        $data = $this->productFactory->create()->getResource()->getProductsSku($ids);
        foreach ($data as $row) {
            $list[$row['sku']] = $row['entity_id'];
        }

        return $this->jsonEncoder->encode($list);
    }

    /**
     * @return string
     */
    public function getSendUrl()
    {
        return $this->getUrl('amasty_multi_inventory/*/send');
    }

    /**
     * @return string
     */
    public function getClearUrl()
    {
        return $this->getUrl('amasty_multi_inventory/*/clear');
    }

    /**
     * @return string
     */
    public function getNextUrl()
    {
        return $this->getUrl('amasty_multi_inventory/*/grid');
    }

    /**
     * @return string
     */
    public function getDeleteFileUrl()
    {
        return $this->getUrl('amasty_multi_inventory/*/deletefile');
    }
}
