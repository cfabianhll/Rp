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



namespace Mirasvit\Rma\Helper;

class Help extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;
    /**
     * @var array
     */
    private $help;

    /**
     * Help constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->help = $this->getHelp();
        $this->context = $context;
        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getHelp()
    {
        return [
            'system' => [
                'general_return_address' => '',
                'general_default_status' => '',
                'general_default_user' => '',
                'general_is_require_shipping_confirmation' => '',
                'general_shipping_confirmation_text' => '',
                'general_is_gift_active' => '',
                'general_is_helpdesk_active' => '',
                'general_brand_attribute' => '',
                'general_file_allowed_extensions' => '',
                'general_file_size_limit' => '',
                'general_rma_grid_columns' => '',
                'frontend_is_active' => '',
                'policy_return_period' => '',
                'policy_allow_in_statuses' => '',
                'policy_is_active' => '',
                'policy_policy_block' => '',
                'number_format' => '',
                'number_counter_start' => '',
                'number_counter_step' => '',
                'number_counter_length' => '',
                'notification_sender_email' => '',
                'notification_customer_email_template' => '',
                'notification_admin_email_template' => '',
                'notification_rule_template' => '',
            ],
        ];
    }

    /************************/
}
