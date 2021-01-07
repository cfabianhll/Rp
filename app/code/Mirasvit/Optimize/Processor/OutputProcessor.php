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



namespace Mirasvit\Optimize\Processor;

use Mirasvit\Optimize\Api\Processor\OutputProcessorInterface;

class OutputProcessor
{
    /**
     * @var OutputProcessorInterface[]
     */
    private $pool;

    public function __construct(
        array $pool = []
    ) {
        ksort($pool);

        $this->pool = $pool;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function process($content)
    {
        foreach ($this->pool as $processor) {
            $content = $processor->process($content);
        }

        return $content;
    }
}
