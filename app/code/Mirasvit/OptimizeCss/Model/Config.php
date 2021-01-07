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



namespace Mirasvit\OptimizeCss\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isMinifyCss()
    {
        return (bool)$this->scopeConfig->getValue('dev/css/minify_files');
    }

    public function isDeferGoogleFont()
    {
        return (bool)$this->scopeConfig->getValue('mst_optimize/optimize_css/defer_google_font');
    }

    public function isMoveCss()
    {
        return $this->scopeConfig->getValue('mst_optimize/optimize_css/move_css');
    }

    /**
     * @param string $css
     *
     * @return bool
     */
    public function isMoveException($css)
    {
        if (strpos($css, 'image/x-icon') !== false) {
            return true;
        }

        $exceptions = explode(PHP_EOL, $this->scopeConfig->getValue('mst_optimize/optimize_css/move_css_exception'));
        $exceptions = array_filter($exceptions);

        foreach ($exceptions as $exception) {
            $exception = trim($exception);

            if (strpos($css, $exception) !== false) {
                return true;
            }
        }

        return false;
    }
}
