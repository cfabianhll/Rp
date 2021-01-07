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



namespace Mirasvit\ImageLazyLoad\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;

class Config
{
    private $scopeConfig;

    private $request;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request     = $request;
    }

    public function isEnabled()
    {
        return $this->scopeConfig->getValue('mst_optimize/optimize_image/image_lazy_load/enabled');
    }

    public function getSkipNumber()
    {
        return $this->scopeConfig->getValue('mst_optimize/optimize_image/image_lazy_load/skip_number');
    }

    public function isDebug()
    {
        return $this->request->getParam('debug') == 'lazy';
    }

    /**
     * @param string $img
     *
     * @return bool
     */
    public function isException($img)
    {
        $exceptions = explode(
            PHP_EOL,
            $this->scopeConfig->getValue('mst_optimize/optimize_image/image_lazy_load/exception')
        );
        $exceptions = array_filter($exceptions);

        foreach ($exceptions as $exception) {
            $exception = trim($exception);

            if (strpos($img, $exception) !== false) {
                return true;
            }
        }

        return false;
    }
}
