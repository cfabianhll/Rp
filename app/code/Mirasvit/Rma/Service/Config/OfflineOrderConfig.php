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

class OfflineOrderConfig implements \Mirasvit\Rma\Api\Config\OfflineOrderConfigInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * OfflineOrderConfig constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig   = $scopeConfig;
    }

    /**
     * @param null|int $store
     * @return bool
     */
    public function isOfflineOrdersEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/general/is_offline_orders',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }
}
