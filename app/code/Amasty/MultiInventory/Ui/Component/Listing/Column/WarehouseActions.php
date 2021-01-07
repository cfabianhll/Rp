<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class WarehouseActions
 */
class WarehouseActions extends Column
{
    /**
     * Url path
     */
    const URL_PATH_EDIT = 'amasty_multi_inventory/warehouse/edit';

    const URL_PATH_DELETE = 'amasty_multi_inventory/warehouse/delete';

    const URL_PATH_DUPLICATE = 'amasty_multi_inventory/warehouse/duplicate';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
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
     * @param array $items
     * @return array
     */
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['warehouse_id'])) {
                    if (!$item['is_general']) {
                        $item[$this->getData('name')] = [
                            'edit' => [
                                'href' => $this->urlBuilder->getUrl(
                                    static::URL_PATH_EDIT,
                                    [
                                        'warehouse_id' => $item['warehouse_id']
                                    ]
                                ),
                                'label' => __('Edit')
                            ]
                        ];
                        $item[$this->getData('name')]['duplicate'] = [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DUPLICATE,
                                [
                                    'warehouse_id' => $item['warehouse_id']
                                ]
                            ),
                            'label' => __('Duplicate'),
                            'confirm' => [
                                'title' => __('Duplicate "${ $.$data.title }"'),
                                'message' => __('Are you sure you want to duplicate a "${ $.$data.title }" record?')
                            ]
                        ];
                        $item[$this->getData('name')]['delete'] = [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'warehouse_id' => $item['warehouse_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete "${ $.$data.title }"'),
                                'message' => __('Are you sure you want to delete a "${ $.$data.title }" record?')
                            ]
                        ];
                    }
                }
            }
        }

        return $dataSource;
    }
}
