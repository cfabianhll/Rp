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



namespace Mirasvit\OptimizeJs\Processor;

use Mirasvit\Optimize\Api\Processor\OutputProcessorInterface;
use Mirasvit\OptimizeJs\Model\Config;

class MoveToBottomProcessor implements OutputProcessorInterface
{
    private $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    public function process($content)
    {
        if (!$this->config->isMoveJs()) {
            return $content;
        }

        if ($this->config->isMoveJsUrlException()) {
            return $content;
        }

        preg_match_all('#(<script.*?</script>)#is', $content, $matches);

        $js = '';
        foreach ($matches[0] as $value) {
            if ($this->config->isMoveJsException($value)) {
                continue;
            }
            $js .= $value;
        }

        $content = preg_replace_callback('#<script.*?</script>#is', [$this, 'replaceCallback'], $content);

        $content .= $js;

        return $content;
    }

    protected function replaceCallback(array $match)
    {
        return $this->config->isMoveJsException($match[0]) ? $match[0] : '';
    }
}
