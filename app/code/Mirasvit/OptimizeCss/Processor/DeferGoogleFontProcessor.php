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



namespace Mirasvit\OptimizeCss\Processor;

use Mirasvit\Optimize\Api\Processor\OutputProcessorInterface;
use Mirasvit\OptimizeCss\Model\Config;

class DeferGoogleFontProcessor implements OutputProcessorInterface
{
    private $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    public function process($content)
    {
        if (!$this->config->isDeferGoogleFont()) {
            return $content;
        }

        $content = str_replace(
            '//fonts.googleapis.com/css?',
            '//fonts.googleapis.com/css?display=swap&',
            $content
        );

        return $content;
    }
}
