<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class ExportButton
 */
class ExportButton extends \Magento\Ui\Component\ExportButton
{
    /**
     * @var ArrayInterface
     */
    private $warehouses;

    /**
     * ExportButton constructor.
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param ArrayInterface|null $warehouses
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        $warehouses = null,
        array $components = [],
        array $data = []
    ) {
        $this->warehouses = $warehouses;
        parent::__construct($context, $urlBuilder, $components, $data);
    }

    /**
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (isset($this->warehouses)) {
            if (!isset($config['warehouses'])) {
                $config['warehouses'] = [];
            }
            $options = $this->warehouses->toOptionArray();
            if (empty($config['rawOptions'])) {
                $options = $this->convertOptionsValueToString($options);
            }
            $config['warehouses'] = array_values(array_merge_recursive($config['warehouses'], $options));
        }
        $this->setData('config', (array)$config);
        parent::prepare();
    }

    /**
     * @param array $options
     * @return array
     */
    private function convertOptionsValueToString(array $options)
    {
        array_walk($options, function (&$value) {
            if (isset($value['value']) && is_scalar($value['value'])) {
                $value['value'] = (string)$value['value'];
            }
        });

        return $options;
    }
}
