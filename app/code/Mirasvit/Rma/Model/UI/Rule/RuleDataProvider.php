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


namespace Mirasvit\Rma\Model\UI\Rule;

use Mirasvit\Rma\Api\Data\RuleInterface;
use Mirasvit\Rma\Model\ResourceModel\Rule\CollectionFactory;
use Mirasvit\Rma\Model\ResourceModel\Rule;
use Mirasvit\Rma\Helper\Storeview;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;

class RuleDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
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
     * RuleDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $ruleCollectionFactory
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
        CollectionFactory $ruleCollectionFactory,
        UrlInterface $url,
        RequestInterface $requestInterface,
        Storeview $rmaStoreview,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $ruleCollectionFactory->create();
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
        foreach ($items as $rule) {
            $rule->setStoreId($this->getConfigData()['store_id']);

            $data = $rule->getData();
            $data[RuleInterface::KEY_NAME] =
                $this->storeview->getStoreViewValue($rule, RuleInterface::KEY_NAME);
            $data[RuleInterface::KEY_EMAIL_BODY] =
                $this->storeview->getStoreViewValue($rule, RuleInterface::KEY_EMAIL_BODY);
            $data[RuleInterface::KEY_EMAIL_SUBJECT] =
                $this->storeview->getStoreViewValue($rule, RuleInterface::KEY_EMAIL_SUBJECT);

            $this->_loadedData[$rule->getRuleId()] = $data;
        }
        return $this->_loadedData;
    }
}