<?php

/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\ReportSuccess\Setup;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Magestore\ReportSuccess\Model\ResourceModel\Product as ProductResource;

/**
 * Webpos Setup Upgrade Data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var ProductResource
     */
    protected $productResource;

    /**
     * UpgradeData constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Framework\App\State $appState
     * @param ProductResource $productResource
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Framework\App\State $appState,
        ProductResource $productResource
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->_appState = $appState;
        $this->productResource = $productResource;
    }

    /**
     * @inheritdoc
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->_appState->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_ADMINHTML,
            function () use ($setup, $context) {
                if (version_compare($context->getVersion(), '1.0.1', '<')) {
                    /** @var EavSetup $eavSetup */
                    $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
                    $eavSetup->updateAttribute(
                        ProductAttributeInterface::ENTITY_TYPE_CODE,
                        'mac',
                        'default_value',
                        null
                    );
                    $eavSetup->updateAttribute(
                        ProductAttributeInterface::ENTITY_TYPE_CODE,
                        'mac',
                        'default_value',
                        null
                    );
                }
                if (version_compare($context->getVersion(), '1.0.2', '<')) {
                    $this->productResource->initMacValue();
                }
            }
        );

        $setup->endSetup();
    }
}
