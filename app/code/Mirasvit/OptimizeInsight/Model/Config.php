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
 * @package   mirasvit/module-optimize
 * @version   1.0.5
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\OptimizeInsight\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config
{
    private $storeManager;

    private $scopeConfig;

    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig  = $scopeConfig;
    }

    /**
     * @return string[]
     */
    public function getMonitoredURLs()
    {
        $baseUrl = $this->storeManager->getDefaultStoreView()->getBaseUrl();
        $value   = $this->scopeConfig->getValue('mst_optimize/optimize_insight/config/URLs');

        $urls = array_filter(array_merge([$baseUrl], explode(PHP_EOL, $value)));

        foreach ($urls as $k => $v) {
            $urls[$k] = trim($v);
        }

        return $urls;
    }
}
