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
 * @package   mirasvit/module-credit
 * @version   1.0.79
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Credit\Reports;

use Mirasvit\Report\Model\AbstractReport;

class Overview extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('Store Credit');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'credit_overview';
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable('mst_credit_transaction');

        $this->setPrimaryFilters([
            'mst_credit_transaction|created_at',
        ]);

        $this->setColumns([
            'mst_credit_transaction|balance_delta__sum',
            'mst_credit_transaction|positive_balance_delta__sum',
            'mst_credit_transaction|negative_balance_delta__sum',
            'mst_credit_transaction|quantity',
        ]);

        $this->setDimensions([
            'mst_credit_transaction|created_at__day',
        ]);

        $this->setPrimaryDimensions([
            'mst_credit_transaction|created_at__day',
            'mst_credit_transaction|created_at__week',
            'mst_credit_transaction|created_at__month',
            'mst_credit_transaction|created_at__year',
        ]);

        $this->getChartConfig()
            ->setType('column')
            ->setDefaultColumns([
                'mst_credit_transaction|balance_delta__sum',
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
