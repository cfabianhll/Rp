<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\MassAction;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class FileFilter
 */
class FileFilter extends \Magento\Ui\Component\MassAction\Filter
{
    const WAREHOUSE_PARAM = 'warehouses';

    const WAREHOUSE_FIELD = 'wis.warehouse_id';

    /**
     * @throws LocalizedException
     */
    public function applySelectionOnTargetProvider()
    {
        $warehouses = $this->getWarehouses();

        $component = $this->getComponent();
        $this->prepareComponent($component);

        $dataProvider = $component->getContext()->getDataProvider();
        $this->joinWarehouse($dataProvider->getSearchResult());
        try {
            if (is_array($warehouses) && !empty($warehouses)) {
                $collection = $dataProvider->getSearchResult();
                $collection->getSelect()->where(self::WAREHOUSE_FIELD . ' in(?)', $warehouses);
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        parent::applySelectionOnTargetProvider();
    }

    /**
     * @return mixed
     */
    public function getWarehouses()
    {
        return $this->request->getParam(static::WAREHOUSE_PARAM);
    }

    /**
     * @param $collection
     */
    public function joinWarehouse($collection)
    {
        $select = $collection
            ->getSelect();

        if (!array_key_exists('wis', $select->getPart('from'))) {
            $select->joinLeft(
                [
                    'wis' => $collection
                        ->getTable('amasty_multiinventory_warehouse_item')
                ],
                'wis.product_id = e.entity_id',
                ['wis.warehouse_id', 'wis.qty']
            );
        }

        $select->joinLeft(
            [
                'amw' => $collection
                    ->getTable('amasty_multiinventory_warehouse')
            ],
            'amw.warehouse_id = wis.warehouse_id',
            ['amw.code']
        );
    }
}
