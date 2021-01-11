<?php
/**
 * Copyright © 2017 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Giftvoucher\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\App\RequestInterface;
use Magestore\Giftvoucher\Model\Product\Type\Giftvoucher as GiftvoucherProduct;

/**
 * Class adds Downloadable collapsible panel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Giftvoucher extends AbstractModifier
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Giftvoucher constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param RequestInterface $request
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        RequestInterface $request,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->objectManager = $objectManager;
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->request = $request;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @inheritDoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function modifyMeta(array $meta)
    {
        $productType = $this->request->getParam('type');
        $product = $this->locator->getProduct();
        if ($productType == GiftvoucherProduct::GIFT_CARD_TYPE ||
            $product->getTypeId() == GiftvoucherProduct::GIFT_CARD_TYPE
        ) {
            if ($this->moduleManager->isEnabled('Magento_InventoryCatalogApi')) {
                /** @var \Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface $isSingleSourceMode */
                $isSingleSourceMode = $this->objectManager->get(
                    \Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface::class
                );
                if (!$isSingleSourceMode->execute()) {
                    $meta = array_replace_recursive(
                        $meta,
                        ['advanced_inventory_modal' => [
                            'children' => [
                                'stock_data' => [
                                    'children' => [
                                        'qty' => [
                                            'arguments' => [
                                                'data' => [
                                                    'config' => [
                                                        'visible' => 0,
                                                        'imports' => ''
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]]
                    );
                }
            }

            $meta = $this->customizeWeightField($meta);
            $meta = $this->removeHasWeightField($meta);
        }

        return $meta;
    }

    /**
     * Customize weight field to depend on gift card type value
     *
     * @param array $meta
     * @return array
     */
    protected function customizeWeightField(array $meta)
    {
        $elementPath = $this->arrayManager->findPath(
            ProductAttributeInterface::CODE_WEIGHT,
            $meta,
            null,
            'children'
        );
        $groupCode = $this->arrayManager->slicePath($elementPath, 0, 1);
        $meta = $this->arrayManager->merge(
            $elementPath . static::META_CONFIG_PATH,
            $meta,
            [
                'component' => 'Magestore_Giftvoucher/js/form/element/weight-input',
                'imports' => [
                    'isDisableWeightField' => 'product_form.product_form.' . $groupCode . '.' . static::CONTAINER_PREFIX
                        . 'gift_card_type' . '.' . 'gift_card_type' . ':value'
                ]
            ]
        );

        return $meta;
    }

    /**
     * Remove "Product Has Weight" field
     *
     * @param array $meta
     * @return array
     */
    public function removeHasWeightField(array $meta)
    {
        return $this->arrayManager->remove(
            $this->arrayManager->findPath(ProductAttributeInterface::CODE_HAS_WEIGHT, $meta, null, 'children'),
            $meta
        );
    }
}
