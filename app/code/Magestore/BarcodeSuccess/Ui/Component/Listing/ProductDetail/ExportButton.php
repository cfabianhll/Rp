<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\BarcodeSuccess\Ui\Component\Listing\ProductDetail;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class ExportButton
 *
 * Used to export
 */
class ExportButton extends \Magento\Ui\Component\ExportButton
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * ExportButton constructor.
     *
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        RequestInterface $request,
        array $components = [],
        array $data = []
    ) {
        $this->request = $request;
        parent::__construct($context, $urlBuilder, $components, $data);
    }

    /**
     * Prepare
     *
     * @return void
     */
    public function prepare()
    {
        $productId = $this->request->getParam('product_id');
        if ($productId) {
            $config = $this->getData('config');
            if (isset($config['options'])) {
                $options = [];
                foreach ($config['options'] as $option) {
                    if (isset($option['value'])) {
                        $option['url'] = $this->urlBuilder->getUrl(
                            $option['url'],
                            [
                                'product_id' => $productId
                            ]
                        );
                        $options[] = $option;
                    }
                }
                $config['options'] = $options;
                $this->setData('config', $config);
            }
        }
        parent::prepare();
    }
}
