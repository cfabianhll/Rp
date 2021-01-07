<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component;

use Amasty\MultiInventory\Ui\Component\Element\ComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class ColumnFactory
 */
class ColumnFactory
{
    /**
     * @var ComponentFactory
     */
    protected $componentFactory;

    /**
     * @var array
     */
    protected $jsComponentMap = [
        'text' => 'Amasty_MultiInventory/js/grid/columns/combi',
        'select' => 'Magento_Ui/js/grid/columns/select',
        'multiselect' => 'Magento_Ui/js/grid/columns/select',
        'date' => 'Magento_Ui/js/grid/columns/date'
    ];

    /**
     * ColumnFactory constructor.
     *
     * @param ComponentFactory $componentFactory
     */
    public function __construct(ComponentFactory $componentFactory)
    {
        $this->componentFactory = $componentFactory;
    }

    /**
     * @param $warehouse
     * @param $context
     * @param array $config
     *
     * @return UiComponentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($warehouse, $context, array $config = [])
    {
        $columnName = $warehouse['code'];
        $array = [
            'label' => __($warehouse['title']),
            'dataType' => 'text',
            'visible' => true,
            'filter' =>  $warehouse['filter']
        ];
        if (!$warehouse['is_general'] && $warehouse['editor']) {
            $array['editor'] =  ['editorType' => 'text'];
            if ($warehouse['validation']) {
                $array['editor']['validation'] = ['required-entry' => true];
            }
        }
        $config = array_merge($array, $config);
        $config['component'] = $this->getJsComponent($config['dataType']);
        $arguments = [
            'data' => [
                'config' => $config,
            ],
            'context' => $context
        ];
        $this->componentFactory->setClass($warehouse['class']);
        return $this->componentFactory->create($columnName, 'column', $arguments);
    }

    /**
     * @param string $dataType
     * @return string
     */
    protected function getJsComponent($dataType)
    {
        return $this->jsComponentMap[$dataType];
    }
}
