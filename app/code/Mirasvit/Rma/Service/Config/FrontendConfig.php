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

class FrontendConfig implements \Mirasvit\Rma\Api\Config\FrontendConfigInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * FrontendConfig constructor.
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
    public function isGiftActive($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/general/is_gift_active',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isFrontendActive($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/frontend/is_active',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function showGuestRmaByOrder($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/frontend/show_guest_rma_by_order',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
