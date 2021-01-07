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



namespace Mirasvit\OptimizeJs\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;

class Config
{
    private $scopeConfig;

    /**
     * @var RequestInterface
     */
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
        return $this->scopeConfig->getValue('mst_optimize/optimize_js/enabled');
    }

    public function isMinifyJs()
    {
        return $this->isEnabled() && $this->scopeConfig->getValue('dev/js/minify_files');
    }

    public function isMoveJs()
    {
        return $this->isEnabled() && $this->scopeConfig->getValue('mst_optimize/optimize_js/move_js');
    }

    public function isMoveJsUrlException()
    {
        $patterns = array_filter(explode(
            PHP_EOL,
            $this->scopeConfig->getValue('mst_optimize/optimize_js/move_js_url_exception')
        ));

        foreach ($patterns as $pattern) {
            if (!trim($pattern)) {
                continue;
            }

            if (strpos($this->request->getRequestUri(), $pattern) !== false) {
                return true;
            }

            if ($this->request->getFullActionName() === $pattern) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $js
     *
     * @return bool
     */
    public function isMoveJsException($js)
    {
        $patterns = array_filter(explode(
            PHP_EOL,
            $this->scopeConfig->getValue('mst_optimize/optimize_js/move_js_url_exception')
        ));

        foreach ($patterns as $pattern) {
            if (!trim($pattern)) {
                continue;
            }

            if (strpos($js, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}
