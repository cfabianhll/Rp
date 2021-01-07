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

class Overview extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('RMA: Report by Status');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'rma_overview';
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable('mst_rma_rma');
        $this->addFastFilters(['mst_rma_rma|created_at']);
        $this->setDefaultColumns([
            'mst_rma_rma|pending_rma_cnt',
            'mst_rma_rma|approved_rma_cnt',
            'mst_rma_rma|rejected_rma_cnt',
            'mst_rma_rma|sent_rma_cnt',
            'mst_rma_rma|closed_rma_cnt',
            'mst_rma_rma|total_rma_cnt',
        ]);

        $this->addColumns([
            'mst_rma_rma|created_at__quarter',
        ]);

        $this->addColumns($this->context->getProvider()->getSimpleColumns('mst_rma_rma'));

        $this->addFastFilters([
            'mst_rma_rma|created_at',
        ]);

        $this->setDefaultDimension('mst_rma_rma|created_at__day');

        $this->addDimensions([
            'mst_rma_rma|created_at__day',
            'mst_rma_rma|created_at__week',
            'mst_rma_rma|created_at__month',
            'mst_rma_rma|created_at__year',
        ]);

        $this->getChartConfig()
            ->setType('column')
            ->setDefaultColumns([
                'mst_rma_rma|pending_rma_cnt',
            ]);
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