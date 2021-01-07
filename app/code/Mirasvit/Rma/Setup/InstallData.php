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
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * InstallData constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) 
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        // Install additional attribute for product
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'rma_allowed');
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'rma_allowed',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Return Allowed',
                'input' => 'select',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 1,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => '',
                'group' => 'RMA',
                'sort_order' => 99,
            ]
        );

        // Install base RMA data
        $data = [
            [
                'condition_id' => 1,
                'name'         => 'Unopened',
                'sort_order'   => 10,
                'is_active'    => 1,
            ],
            [
                'condition_id' => 2,
                'name'         => 'Opened',
                'sort_order'   => 20,
                'is_active'    => 1,
            ],
            [
                'condition_id' => 3,
                'name'         => 'Damaged',
                'sort_order'   => 30,
                'is_active'    => 1,
            ],
        ];
        foreach ($data as $row) {
            $setup->getConnection()->insertForce($setup->getTable('mst_rma_condition'), $row);
        }

        $data = [
            [
                'reason_id'  => 1,
                'name'       => 'Out of Service',
                'sort_order' => 10,
                'is_active'  => 1,
            ],
            [
                'reason_id'  => 2,
                'name'       => 'Don\'t like',
                'sort_order' => 20,
                'is_active'  => 1,
            ],
            [
                'reason_id'  => 3,
                'name'       => 'Wrong color',
                'sort_order' => 30,
                'is_active'  => 1,
            ],
            [
                'reason_id'  => 4,
                'name'       => 'Wrong color',
                'sort_order' => 30,
                'is_active'  => 1,
            ],
            [
                'reason_id'  => 5,
                'name'       => 'Wrong size',
                'sort_order' => 40,
                'is_active'  => 1,
            ],
            [
                'reason_id'  => 6,
                'name'       => 'Other',
                'sort_order' => 50,
                'is_active'  => 1,
            ],
        ];
        foreach ($data as $row) {
            $setup->getConnection()->insertForce($setup->getTable('mst_rma_reason'), $row);
        }

        $data = [
            [
                'resolution_id' => 1,
                'name'          => 'Exchange',
                'sort_order'    => 10,
                'is_active'     => 1,
                'code'          => 'exchange',
            ],
            [
                'resolution_id' => 2,
                'name'          => 'Refund',
                'sort_order'    => 20,
                'is_active'     => 1,
                'code'          => 'refund',
            ],
            [
                'resolution_id' => 3,
                'name'          => 'Store Credit',
                'sort_order'    => 30,
                'is_active'     => 1,
                'code'          => 'credit',
            ],
        ];
        foreach ($data as $row) {
            $setup->getConnection()->insertForce($setup->getTable('mst_rma_resolution'), $row);
        }
// @codingStandardsIgnoreStart
        $data = [
            [
                'status_id'        => 1,
                'name'             => 'Pending Approval',
                'sort_order'       => 10,
                'is_active'        => 1,
                'code'             => 'pending',
                'is_show_shipping'  => 0,
                'customer_message' =>
"Dear {{var customer.name}},
<br><br>\n\nYour Return request has been received.
You will be notified when your request is reviewed.",
'admin_message'    => "RMA #{{var rma.increment_id}} has been created.",
'history_message'  => "Return request has been received.
You will be notified when your request is reviewed.",
            ],
            [
                'status_id'        => 2,
                'name'             => 'Approved',
                'sort_order'       => 20,
                'is_active'        => 1,
                'code'             => 'approved',
                'is_show_shipping'  => 1,
                'customer_message' => "Dear {{var customer.name}},
<br><br>\n\nYour Return request has been approved.\n
<br>\nPlease, print <a href='{{var rma.guest_print_url}}'>RMA Packing Slip</a>
{{depend rma.guest_print_label_url}},
<a href='{{var rma.guest_print_label_url}}'>RMA Shipping Label</a>
\n{{/depend}} and send package to:<br>\n{{var rma.return_address_html | raw}}",
'admin_message'    => '',
'history_message'  => "Your Return request has been approved.\n
<br>\nPlease, print <a href='{{var rma.guest_print_url}}'>RMA Packing Slip</a>
{{depend rma.guest_print_label_url}},
<a href='{{var rma.guest_print_label_url}}'>RMA Shipping Label</a>
\n{{/depend}} and send package to:<br>\n{{var rma.return_address_html | raw}}",
            ],
            [
                'status_id'        => 3,
                'name'             => 'Rejected',
                'sort_order'       => 30,
                'is_active'        => 1,
                'code'             => 'rejected',
                'is_show_shipping'  => 0,
                'customer_message' => "Dear {{var customer.name}},<br><br>Return request has been rejected.",
                'admin_message'    => '',
                'history_message'  => 'Return request has been rejected.',
            ],
            [
                'status_id'        => 4,
                'name'             => 'Package Sent',
                'sort_order'       => 25,
                'is_active'        => 1,
                'code'             => 'package_sent',
                'is_show_shipping'  => 0,
                'customer_message' => '',
                'admin_message'    => 'Package is sent.',
                'history_message'  => '',
            ],
            [
                'status_id'        => 5,
                'name'             => 'Closed',
                'sort_order'       => 100,
                'is_active'        => 1,
                'code'             => 'closed',
                'is_show_shipping'  => 0,
                'customer_message' => 'Dear {{var customer.name}},<br><br>Your Return request has been closed.',
                'admin_message'    => '',
                'history_message'  => 'Return request has been closed.',
            ],
        ];
// @codingStandardsIgnoreEnd
        foreach ($data as $row) {
            $setup->getConnection()->insertForce($setup->getTable('mst_rma_status'), $row);
        }
    }
}
