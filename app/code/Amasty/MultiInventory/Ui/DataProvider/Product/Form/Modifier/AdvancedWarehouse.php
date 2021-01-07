<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\DataProvider\Product\Form\Modifier;

use Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface;
use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Data provider for advanced inventory form
 */
class AdvancedWarehouse extends AbstractModifier
{
    const STOCK_DATA_FIELDS = 'warehouse_data';
    const QTY_CONTAINER_PATH = '/children/quantity_and_stock_status_qty/children';
    const STOCK_CONTAINER_PATH = '/children/container_quantity_and_stock_status/children';

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var System
     */
    private $system;

    /**
     * @var WarehouseFactory
     */
    private $factory;

    /**
     * @var array
     */
    private $meta = [];

    /**
     * @var WarehouseItemRepositoryInterface
     */
    private $stockRepository;

    private $fieldsetPath;

    /**
     * AdvancedWarehouse constructor.
     *
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param System $system
     * @param WarehouseItemRepositoryInterface $stockRepository
     * @param WarehouseFactory $factory
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        System $system,
        WarehouseItemRepositoryInterface $stockRepository,
        WarehouseFactory $factory
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->system = $system;
        $this->factory = $factory;
        $this->stockRepository = $stockRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->prepareMeta();

        return $this->meta;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        if ($this->system->getAvailableDecreese()) {
            $model = $this->locator->getProduct();
            $modelId = $model->getId();

            $stockItem = $this->stockRepository
                ->getByProductWarehouse($modelId, $this->factory->create()->getDefaultId());

            if ($stockItem->getId()) {
                $pathPrefix = $modelId . '/product/quantity_and_stock_status/';
                $path = $pathPrefix . 'qty';
                $data = $this->arrayManager->set($path, $data, (int)$stockItem->getQty());
                $path = $pathPrefix . 'is_in_stock';
                $data = $this->arrayManager->set($path, $data, $stockItem->getStockStatus());
            }
        }

        return $data;
    }

    /**
     * @return void
     */
    private function prepareMeta()
    {
        $product = $this->locator->getProduct();
        if ($product->getTypeId() != Type::TYPE_SIMPLE
            || !$this->system->isMultiEnabled()
        ) {
            return;
        }

        //get fields in form product
        $pathField = $this->arrayManager->findPath('quantity_and_stock_status', $this->meta, null, 'children');
        if (!$pathField) {
            return;
        }

        //search fieldset in form for quantity_and_stock_status
        $this->fieldsetPath = $this->arrayManager->slicePath($pathField, 0, -4);
        $this->modifyQtyField();
        $this->modifyStockField();

        //add button warehouse after advanced inventory
        $warehouseButton['arguments']['data']['config'] = [
            'displayAsLink' => true,
            'formElement' => 'container',
            'componentType' => 'container',
            'component' => 'Magento_Ui/js/form/components/button',
            'template' => 'ui/form/components/button/container',
            'actions' => [
                [
                    'targetName' => 'product_form.product_form.amasty_multi_inventory_modal',
                    'actionName' => 'toggleModal',
                ],
            ],
            'title' => __('Warehouses'),
            'provider' => false,
            'additionalForGroup' => true,
            'source' => 'product_details',
            'sortOrder' => 30,
        ];
        // add changes for form
        $this->meta = $this->arrayManager->merge(
            $this->fieldsetPath . self::QTY_CONTAINER_PATH,
            $this->meta,
            ['warehouse' => $warehouseButton]
        );
    }

    private function modifyQtyField()
    {
        $textTooltip = __('This field is disabled because of Multiple Stock Locations enabled. Please manage the '
            . 'product qty in the Warehouses section below or from Products>Inventory>Manage Stock');

        //get field qty on form product
        $qty = $this->arrayManager->get($this->fieldsetPath . self::QTY_CONTAINER_PATH . '/qty', $this->meta);

        $qty['arguments']['data']['config']['tooltip']['description'] = $textTooltip;
        // disable field, also disable them by js
        $qty['arguments']['data']['config']['disabled'] = true;

        $this->meta = $this->arrayManager->merge(
            $this->fieldsetPath . self::QTY_CONTAINER_PATH,
            $this->meta,
            ['qty' => $qty]
        );
    }

    private function modifyStockField()
    {
        $textTooltip = __('This field is disabled because of Multiple Stock Locations enabled. Please manage the '
            . 'product stock in the Warehouses section');

        //get field stock status on form product
        $stock = $this->arrayManager->get(
            $this->fieldsetPath . self::STOCK_CONTAINER_PATH . '/quantity_and_stock_status',
            $this->meta
        );

        $stock['arguments']['data']['config']['tooltip']['description'] = $textTooltip;
        // disable field, also disable them by js
        $stock['arguments']['data']['config']['disabled'] = true;

        $this->meta = $this->arrayManager->merge(
            $this->fieldsetPath . self::STOCK_CONTAINER_PATH,
            $this->meta,
            ['quantity_and_stock_status' => $stock]
        );
    }
}
