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


namespace Mirasvit\Credit\Setup\UpgradeData;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class UpgradeData104 implements UpgradeDataInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $storeManager;

    /**
     * UpgradeData104 constructor.
     * @param ScopeConfigInterface $storeManager
     */
    public function __construct(ScopeConfigInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $currencyCode = $this->storeManager->getValue(
            'currency/options/base'
        );
        $setup->getConnection()->update(
            $setup->getTable('mst_credit_balance'),
            ['currency_code' => $currencyCode]
        );
        $setup->getConnection()->update(
            $setup->getTable('mst_credit_transaction'),
            ['currency_code' => $currencyCode]
        );

        $setup->endSetup();
    }
}