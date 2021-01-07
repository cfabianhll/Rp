<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.1.12
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Rma\Service\Item\ItemManagement;

/**
 *  We put here only methods directly connected with Item properties
 */
class Product implements \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface
{
    private $imageHelper;

    private $itemFactory;

    private $itemManagement;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Item Product constructor.
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement,
        \Mirasvit\Rma\Model\ItemFactory $itemFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->itemManagement    = $itemManagement;
        $this->itemFactory       = $itemFactory;
        $this->productRepository = $productRepository;
        $this->imageHelper       = $imageHelper;
        $this->productFactory    = $productFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getProduct(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $product = null;
        try {
            $storeId = $item->getData('store_id', null);
            if ($item->getProductSku()) { //items migrated from M1 do not have ID, only SKU
                $product = $this->productRepository->get($item->getProductSku(), false, $storeId);
            } elseif ($item->getSku()) {
                $product = $this->productRepository->get($item->getSku(), false, $storeId);
            }
            if (!$product && $item->getProductId()) {
                $product = $this->productRepository->getById($item->getProductId(), false, $storeId);
            }
        } catch (\Exception $e) {
            $product = $this->productFactory->create()->setData($item->getData());
        }

        return $product;
    }

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $exchangeProduct;

    /**
     * {@inheritdoc}
     */
    public function getExchangeProduct(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        if (!$this->exchangeProduct) {
            $this->exchangeProduct = $this->productFactory->create()->load($item->getExchangeProductId());
        }

        return $this->exchangeProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function getImage($item, $imageId, $attributes = [])
    {
        $orderItem = $this->itemManagement->getOrderItem($item);
        $item->setProductOptions($orderItem->getProductOptions());
        $options = $item->getProductOptions();
        if (!empty($options['simple_sku'])) {
            $childItem = $this->itemFactory->create()->setSku($options['simple_sku']);
            $product   = $this->getProduct($childItem);
            $image     = $this->imageHelper->init($product, $imageId, $attributes);
            if ($image->getUrl() == $image->getDefaultPlaceholderUrl()) {//if child does not have img, use parent
                $product = $this->getProduct($item);
            }
        } else {
            $product = $this->getProduct($item);
        }
        $image = $this->imageHelper->init($product, $imageId, $attributes);
        $image->setImageFile($product->getSmallImage());
        if ($image->getUrl() == $image->getDefaultPlaceholderUrl()) {
            $product = $this->productFactory->create();
            if (!empty($options['super_product_config'])) {//configurable product
                $product->getResource()->load($product, $options['super_product_config']['product_id']);
            } elseif (!empty($options['info_buyRequest']) && isset($options['info_buyRequest']['product'])) {//others
                $product->getResource()->load($product, $options['info_buyRequest']['product']);
            }
            $image = $this->imageHelper->init($product, $imageId, $attributes);
        }

        return $image;
    }
}
