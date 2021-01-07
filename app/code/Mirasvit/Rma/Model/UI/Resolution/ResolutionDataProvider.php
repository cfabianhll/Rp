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
 * @package   mirasvit/module-rma
 * @version   2.1.12
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Rma\Model\UI\Resolution;

use Mirasvit\Rma\Api\Data\ResolutionInterface;
use Mirasvit\Rma\Model\ResourceModel\Resolution\CollectionFactory;
use Mirasvit\Rma\Helper\Storeview;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;

class ResolutionDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array $_loadedData
     */
    protected $_loadedData;
    /**
     * @var Storeview
     */
    private $storeview;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * AddressDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $resolutionCollectionFactory
     * @param UrlInterface $url
     * @param RequestInterface $requestInterface
     * @param Storeview $rmaStoreview
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $resolutionCollectionFactory,
        UrlInterface $url,
        RequestInterface $requestInterface,
        Storeview $rmaStoreview,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $resolutionCollectionFactory->create();
        $this->url                = $url;
        $this->request            = $requestInterface;
        $this->storeview          = $rmaStoreview;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getConfigData()
    {
        $config = parent::getConfigData();
        $config['submit_url'] = $this->url->getUrl('*/*/save',
            [
                'id' => (int) $this->request->getParam('id'),
                'store' => (int) $this->request->getParam('store')
            ]
        );
        $config['store_id'] = (int) $this->request->getParam('store');
        return $config;
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $resolution) {
            $resolution->setStoreId($this->getConfigData()['store_id']);

            $data = $resolution->getData();
            $data[ResolutionInterface::KEY_NAME] =
                $this->storeview->getStoreViewValue($resolution, ResolutionInterface::KEY_NAME);
            $data['disabled'] = 'true';

            $this->_loadedData[$resolution->getResolutionId()] = $data;
        }
        return $this->_loadedData;
    }
}