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
use Mirasvit\Report\Model\Context;
use Mirasvit\Report\Ui\Context as UiContext;

class Attribute extends AbstractReport
{
    /**
     * @var UiContext
     */
    private $uiContext;

    /**
     * Attribute constructor.
     * @param UiContext $uiContext
     * @param Context $context
     */
    public function __construct(
        UiContext $uiContext,
        Context $context
    ) {
        $this->uiContext = $uiContext;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('RMA: Report by Attribute');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'rma_attribute';
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable('catalog_product_entity');

        $this->setInternalFilters([
            [
                'column'        => 'mst_rma_item|qty_requested',
                'value'         => '0',
                'conditionType' => 'gt',
            ],
        ]);

        $this->addFastFilters([
            'mst_rma_item|created_at',
        ]);

        $this->setRequiredColumns(['catalog_product_entity|entity_id']);

        $this->setDefaultColumns([
            'mst_rma_item|total_rma_cnt',
            'mst_rma_item|total_items_cnt',
            'mst_rma_item|item_qty_requested',
        ]);

        $this->setDefaultDimension('sales_order_item|product_id');

        foreach ($this->context->getProvider()->getSimpleColumns('catalog_product_entity') as $column) {
            $this->addDimensions([$column]);
        }

        $this->addColumns($this->context->getProvider()->getComplexColumns('sales_order_item'));
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