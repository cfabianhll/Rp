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

class UpgradeData1016 implements UpgradeDataInterface
{
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $config;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * UpgradeData1016 constructor.
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $config
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        \Magento\Framework\App\Config\Storage\WriterInterface $config,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->config          = $config;
        $this->productMetadata = $productMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($this->productMetadata->getVersion(), '2.3.3', '==')) {
            $data = [
                ['from' => 'Ỳ', 'to' => 'y'],
                ['from' => 'Ǹ', 'to' => 'n'],
                ['from' => 'Ẁ', 'to' => 'w'],
            ];
            foreach ($data as $k => $v) {
                $this->config->save('url/convert/'.$k.'/from', $v['from'], 'default');
                $this->config->save('url/convert/'.$k.'/to', $v['to'], 'default');
            }
        }

        $setup->endSetup();
    }
}