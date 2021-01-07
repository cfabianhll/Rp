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



namespace Mirasvit\Optimize\Plugin\Framework\Controller\Result;

use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\App\ResponseInterface;
use Mirasvit\Optimize\Processor\OutputProcessor;

;

/**
 * @see \Magento\Framework\Controller\ResultInterface
 */
class OutputProcessorPlugin
{
    /** @var Request */
    private $request;

    /** @var OutputProcessor */
    private $outputProcessor;

    public function __construct(
        Request $request,
        OutputProcessor $outputProcessor
    ) {
        $this->request         = $request;
        $this->outputProcessor = $outputProcessor;
    }

    /**
     * @param \Magento\Framework\Controller\ResultInterface $subject
     * @param \Closure                                      $proceed
     * @param ResponseInterface                             $response
     *
     * @return mixed
     */
    public function aroundRenderResult($subject, \Closure $proceed, ResponseInterface $response)
    {
        $result = $proceed($response);

        if ($this->request->isAjax()
            || strpos($this->request->getRequestUri(), 'paypal') !== false
            || $this->request->isPost()
        ) {
            return $result;
        }

        $content = $response->getBody();
        $content = $this->outputProcessor->process($content);
        $response->setBody($content);

        return $result;
    }
}
