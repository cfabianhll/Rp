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
use Mirasvit\Rma\Api\Config\RmaRequirementConfigInterface;

class RmaRequirementConfig implements \Mirasvit\Rma\Api\Config\RmaRequirementConfigInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * RmaRequirementConfig constructor.
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
    public function getGeneralCustomerRequirement($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/frontend/rma_customer_requirement',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isCustomerReasonRequired($store = null)
    {
        $config = $this->getGeneralCustomerRequirement($store);
        $data = explode(',', $config);

        return in_array(RmaRequirementConfigInterface::RMA_CUSTOMER_REQUIRES_REASON, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function isCustomerConditionRequired($store = null)
    {
        $config = $this->getGeneralCustomerRequirement($store);
        $data = explode(',', $config);

        return in_array(RmaRequirementConfigInterface::RMA_CUSTOMER_REQUIRES_CONDITION, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function isCustomerResolutionRequired($store = null)
    {
        $config = $this->getGeneralCustomerRequirement($store);
        $data = explode(',', $config);

        return in_array(RmaRequirementConfigInterface::RMA_CUSTOMER_REQUIRES_RESOLUTION, $data);
    }
}
