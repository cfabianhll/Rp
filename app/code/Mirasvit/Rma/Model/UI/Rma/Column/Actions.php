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


namespace Mirasvit\Rma\Model\UI\Rma\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class ProductActions
 */
class Actions extends Column
{
    /**
     * @var int|null
     */
    private $storeId = -1;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as $k => $item) {
            $dataSource['data']['items'][$k] = $this->addAddRmaUrl($item);
        }

        return $dataSource;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    private function addAddRmaUrl($item)
    {
        $params = [
            'customer_id' => $item['entity_id'],
            'store'       => $this->getStoreId(),
            'orders_id'   => 'offline',
        ];
        $item[$this->getData('name')]['select'] = [
            'href'   => $this->getRmaUrl($params),
            'label'  => __('Select'),
            'hidden' => false,
        ];

        return $item;
    }

    /**
     * @return int
     */
    private function getStoreId()
    {
        if ($this->storeId == -1) {
            $this->storeId = $this->context->getFilterParam('store_id');
        }
        return $this->storeId;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    private function getRmaUrl($params)
    {
        return $this->urlBuilder->getUrl(
            'rma/rma/add',
            $params
        );
    }
}
