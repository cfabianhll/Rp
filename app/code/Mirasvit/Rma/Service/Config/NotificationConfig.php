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



namespace Mirasvit\Rma\Service\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class NotificationConfig implements \Mirasvit\Rma\Api\Config\NotificationConfigInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * NotificationConfig constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getSenderEmail($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/notification/sender_email',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerEmailTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/notification/customer_email_template',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminEmailTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/notification/admin_email_template',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/notification/rule_template',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSendEmailMethod($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/notification/send_email_bcc_type',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSendEmailBcc($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/notification/send_email_bcc',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

}
