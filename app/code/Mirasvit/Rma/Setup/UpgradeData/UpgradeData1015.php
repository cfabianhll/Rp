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
use Mirasvit\Rma\Api\Data\RmaInterface;
use Mirasvit\Rma\Api\Data\StatusInterface;

class UpgradeData1015 implements UpgradeDataInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $table = $setup->getTable('mst_rma_rma');
        $select = $setup->getConnection()->select()
            ->from(
                ['main_table' => $table],
                [
                    RmaInterface::KEY_STATUS_HISTORY => RmaInterface::KEY_STATUS_ID,
                ]
            );

        $select = $setup->getConnection()->updateFromSelect(
            $select,
            $table
        );

        $setup->getConnection()->query($select);

        $table = $setup->getTable('mst_rma_status');
        $setup->getConnection()->update(
            $table,
            [StatusInterface::KEY_COLOR => 'yellow'],
            [StatusInterface::KEY_CODE . ' = ?' => 'pending']
        );
        $setup->getConnection()->update(
            $table,
            [StatusInterface::KEY_COLOR => 'green'],
            [StatusInterface::KEY_CODE . ' = ?' => 'approved']
        );
        $setup->getConnection()->update(
            $table,
            [StatusInterface::KEY_COLOR => 'orange'],
            [StatusInterface::KEY_CODE . ' = ?' => 'rejected']
        );
        $setup->getConnection()->update(
            $table,
            [StatusInterface::KEY_COLOR => 'teal'],
            [StatusInterface::KEY_CODE . ' = ?' => 'package_sent']
        );

        $setup->endSetup();
    }
}