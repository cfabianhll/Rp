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


namespace Mirasvit\Rma\Setup\UpgradeData;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Mirasvit\Rma\Api\Data\ResolutionInterface;

class UpgradeData1011 implements UpgradeDataInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $table = $setup->getTable('mst_rma_resolution');
        $bind = [
            ResolutionInterface::KEY_CREDITMEMO_ALLOWED => 0,
            ResolutionInterface::KEY_EXCHANGE_ORDER_ALLOWED => 1,
            ResolutionInterface::KEY_REPLACEMENT_ORDER_ALLOWED => 1,
        ];
        $where = [ResolutionInterface::KEY_CODE . ' = ?' => ResolutionInterface::EXCHANGE];
        $setup->getConnection()->update($table, $bind, $where);

        $table = $setup->getTable('mst_rma_resolution');
        $bind = [
            ResolutionInterface::KEY_CREDITMEMO_ALLOWED => 0,
            ResolutionInterface::KEY_EXCHANGE_ORDER_ALLOWED => 1,
            ResolutionInterface::KEY_REPLACEMENT_ORDER_ALLOWED => 1,
        ];
        $where = [ResolutionInterface::KEY_CODE . ' = ?' => ResolutionInterface::REFUND];
        $setup->getConnection()->update($table, $bind, $where);

        $table = $setup->getTable('mst_rma_resolution');
        $bind = [
            ResolutionInterface::KEY_CREDITMEMO_ALLOWED => 1,
            ResolutionInterface::KEY_EXCHANGE_ORDER_ALLOWED => 0,
            ResolutionInterface::KEY_REPLACEMENT_ORDER_ALLOWED => 0,
        ];
        $where = [ResolutionInterface::KEY_CODE . ' = ?' => ResolutionInterface::CREDIT];
        $setup->getConnection()->update($table, $bind, $where);

        $setup->endSetup();
    }
}