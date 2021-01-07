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

class RmaNumberConfig implements \Mirasvit\Rma\Api\Config\RmaNumberConfigInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * RmaNumberConfig constructor.
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
    public function isManualNumberAllowed($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/number/allow_manual',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormat($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/number/format',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isResetCounter($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/number/reset_counter',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCounterStart($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/number/counter_start',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCounterStep($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/number/counter_step',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCounterLength($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/number/counter_length',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
