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


namespace Mirasvit\Rma\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{

    /**
     * @var UpgradeDataInterface[]
     */
    private $pool;

    /**
     * UpgradeData constructor.
     * @param UpgradeData\UpgradeData102 $upgrade102
     * @param UpgradeData\UpgradeData105 $upgrade105
     * @param UpgradeData\UpgradeData1011 $upgrade1011
     * @param UpgradeData\UpgradeData1015 $upgrade1015
     * @param UpgradeData\UpgradeData1016 $upgrade1016
     */
    public function __construct(
        UpgradeData\UpgradeData102 $upgrade102,
        UpgradeData\UpgradeData105 $upgrade105,
        UpgradeData\UpgradeData1011 $upgrade1011,
        UpgradeData\UpgradeData1015 $upgrade1015,
        UpgradeData\UpgradeData1016 $upgrade1016,
        UpgradeData\UpgradeData1017 $upgrade1017
    ) {
        $this->pool = [
            '1.0.2'  => $upgrade102,
            '1.0.5'  => $upgrade105,
            '1.0.11' => $upgrade1011,
            '1.0.15' => $upgrade1015,
            '1.0.16' => $upgrade1016,
            '1.0.17' => $upgrade1017,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        foreach ($this->pool as $version => $upgrade) {
            if (version_compare($context->getVersion(), $version) < 0) {
                $upgrade->upgrade($setup, $context);
            }
        }

        $setup->endSetup();
    }
}
