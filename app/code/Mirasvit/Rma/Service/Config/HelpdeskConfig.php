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

use \Magento\Framework\App\Config\ScopeConfigInterface;

class HelpdeskConfig implements \Mirasvit\Rma\Api\Config\HelpdeskConfigInterface
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * HelpdeskConfig constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->scopeConfig   = $scopeConfig;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param null|int $store
     * @return bool|null
     */
    protected function getIsHelpdeskActive($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/general/is_helpdesk_active',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }


    /**
     * {@inheritdoc}
     */
    public function isHelpdeskActive()
    {
        if ($this->getIsHelpdeskActive() && $this->moduleManager->isEnabled('Mirasvit_Helpdesk')) {
            return true;
        }
    }
}
