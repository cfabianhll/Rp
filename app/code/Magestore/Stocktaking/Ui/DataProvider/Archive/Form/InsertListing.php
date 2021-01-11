<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Ui\DataProvider\Archive\Form;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magestore\Stocktaking\Api\Data\StocktakingArchiveItemInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Product
 *
 * Used for data Product List
 */
class InsertListing extends \Magento\Ui\Component\Container
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * InsertListing constructor.
     *
     * @param ContextInterface $context
     * @param RequestInterface $request
     * @param UrlInterface $url
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        RequestInterface $request,
        UrlInterface $url,
        array $components = [],
        array $data = []
    ) {
        $this->request = $request;
        $this->url = $url;
        parent::__construct($context, $components, $data);
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');
        $url = $this->url->getUrl(
            'mui/index/render',
            [
                StocktakingArchiveItemInterface::STOCKTAKING_ID => $this->request->getParam('id')
            ]
        );
        $config['render_url'] = $url;
        $config['update_url'] = $url;
        $this->setData('config', $config);
        parent::prepare();
    }
}
