<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column\Warehouse;

use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class Column
 */
class Column extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var WarehouseFactory
     */
    private $whFactory;

    /**
     * Column constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param WarehouseFactory $whFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        WarehouseFactory $whFactory,
        array $components = [],
        array $data = []
    ) {
        $this->whFactory = $whFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepare()
    {
        $config = $this->getData('config');
        $config['disabled'] = [$this->whFactory->create()->getDefaultId()];
        $this->setData('config', $config);

        parent::prepare();
    }
}
