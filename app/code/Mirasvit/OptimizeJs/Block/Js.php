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



namespace Mirasvit\OptimizeJs\Block;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\Template;

class Js extends Template
{
    const MODE_PARAM      = 'optimize_js';
    const MODE_BACKGROUND = 'background';
    const MODE_PRE_FLY    = 'pre-fly';

    private $urlBuilder;

    /** @var \Magento\Framework\App\RequestInterface */
    private $request;

    public function __construct(
        Context $context
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->request    = $context->getRequest();

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     * @return string|void
     */
    public function _toHtml()
    {
        if (strpos($this->request->getFullActionName(), 'checkout') !== false) {
            return;
        }
        
        $baseUrl = $this->urlBuilder->getUrl('optimizeJs/bundle/track');

        $initObject = [
            'Mirasvit_OptimizeJs/js/bundle/track' => [
                'callbackUrl' => $baseUrl,
                'layout'      => $this->request->getFullActionName(),
                'mode'        => $this->request->getParam(self::MODE_PARAM, self::MODE_BACKGROUND),
            ],
        ];

        return '<div data-mage-init=\'' . \Zend_Json::encode($initObject) . '\'></div>';
    }
}
