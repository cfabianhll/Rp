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


namespace Mirasvit\Rma\Reports;

use Mirasvit\Report\Model\AbstractReport;

class OfflineProduct extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('RMA: Report by Offline Product');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'rma_offline_product';
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable('mst_rma_offline_item');

        $this->setPrimaryFilters([
            'mst_rma_offline_item|created_at',
            'quote|store_id',
        ]);
        $this->setInternalFilters([
            [
                'column'        => 'mst_rma_offline_item|qty_requested',
                'value'         => '0',
                'conditionType' => 'gt',
            ],
        ]);

        $this->setColumns([
            'mst_rma_offline_item|name',
            'mst_rma_offline_item|total_rma_cnt',
            'mst_rma_offline_item|total_items_cnt',
            'mst_rma_offline_item|item_qty_requested',
        ]);

        $this->setDimensions(
            ['mst_rma_offline_item|name']
        )->setPrimaryDimensions(
            ['mst_rma_offline_item|name']
        );
    }

    /**
     * @return mixed|string[]|null
     */
    public function getApplicableColumns()
    {
        return $this->getColumns();
    }

    /**
     * @return mixed|string[]|null
     */
    public function getApplicableDimensions()
    {
        return $this->getPrimaryDimensions();
    }
}