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

use Magento\Store\Model\ScopeInterface;

class RmaPolicyConfig implements \Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * RmaPolicyConfig constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getReturnPeriod($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/policy/return_period',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowRmaInOrderStatuses($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'rma/policy/allow_in_statuses',
            ScopeInterface::SCOPE_STORE,
            $store
        );

        return explode(',', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowRmaRequestOnlyShipped($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/policy/return_only_shipped',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowMultipleOrders($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/policy/is_allow_multiple_order',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/policy/is_active',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPolicyBlock($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/policy/policy_block',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

}
