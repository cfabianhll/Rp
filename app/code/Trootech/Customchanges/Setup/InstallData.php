<?php

namespace Trootech\Customchanges\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '0.0.1') < 0){





		$eavSetup -> removeAttribute(\Magento\Catalog\Model\Category::ENTITY, 'estimate_shipping');

		
			$eavSetup -> addAttribute(\Magento\Catalog\Model\Category :: ENTITY, 'estimate_shipping', [
                        'type' => 'varchar',
                        'label' => 'Estimate Shipping',
                        'input' => 'text',
						'required' => false,
                        'sort_order' => 110,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                        'group' => 'General Information',
						"default" => "",
						"class"    => "",
						"note"       => ""
			]
			);
					

	


				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$salesSetup = $objectManager->create('Magento\Sales\Setup\SalesSetup');
				
$salesSetup->addAttribute('order', 'shipping_type', ['type' =>'varchar']);
$salesSetup->addAttribute('order_item', 'shipping_type', ['type' =>'varchar']);


$salesSetup->addAttribute('order', 'pickup_store_id', ['type' =>'varchar']);
$salesSetup->addAttribute('order_item', 'pickup_store_id', ['type' =>'varchar']);


$salesSetup->addAttribute('order', 'identification_card', ['type' =>'varchar']);
$salesSetup->addAttribute('order_item', 'identification_card', ['type' =>'varchar']);

$salesSetup->addAttribute('order', 'township', ['type' =>'varchar']);
$salesSetup->addAttribute('order_item', 'township', ['type' =>'varchar']);



$quoteSetup = $objectManager->create('Magento\Quote\Setup\QuoteSetup');
$quoteSetup->addAttribute('quote', 'shipping_type', ['type' =>'varchar']);
$quoteSetup->addAttribute('quote_item', 'shipping_type', ['type' =>'varchar']);

$quoteSetup->addAttribute('quote', 'pickup_store_id', ['type' =>'varchar']);
$quoteSetup->addAttribute('quote_item', 'pickup_store_id', ['type' =>'varchar']);

$quoteSetup->addAttribute('quote', 'identification_card', ['type' =>'varchar']);
$quoteSetup->addAttribute('quote_item', 'identification_card', ['type' =>'varchar']);

$quoteSetup->addAttribute('quote', 'township', ['type' =>'varchar']);
$quoteSetup->addAttribute('quote_item', 'township', ['type' =>'varchar']);
				
		}

    }
}