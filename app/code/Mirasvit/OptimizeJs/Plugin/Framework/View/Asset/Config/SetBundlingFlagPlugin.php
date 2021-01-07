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



namespace Mirasvit\OptimizeJs\Plugin\Framework\View\Asset\Config;

use Mirasvit\OptimizeJs\Model\Config;

/**
 * @see \Magento\Framework\View\Asset\Config
 */
class SetBundlingFlagPlugin
{
    private $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    public function afterIsBundlingJsFiles()
    {
        return $this->config->isEnabled();
    }
}
