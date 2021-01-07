<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\System\Config\Field;

use Amasty\MultiInventory\Traits\Additional;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class PriorityValues
 */
class PriorityValues extends Field
{
    use Additional;

    /**
     * @var string
     */
    protected $_template = 'Amasty_MultiInventory::system/config/form/field/options.phtml';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    const STOCK_PATH = 'amasty_multi_inventory/stock/';

    const PRIORITY = 'priority';

    const IS_ACTIVE = 'is_active';

    /**
     * PriorityValues constructor.
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setElement($element);

        return $this->_toHtml();
    }

    /**
     * @return mixed
     */
    public function getDataOptions()
    {
        $data = $this->getElement()->getData('value');
        $field = $this->getElement()->getData('field_config');
        $data = $this->setValues($data, $field['id']);
        uasort($data, ["self", "sortPriority"]);

        return $data;
    }

    /**
     * @return mixed|object
     */
    private function getScopeConfig()
    {
        return $this->scopeConfig;
    }

    /**
     * From Config set parameters with Drag & Drop
     *
     * @param $data
     * @param $id
     * @return mixed
     */
    private function setValues($data, $id)
    {
        foreach ($data as $index => $element) {
            $data[$index][self::PRIORITY] = $this->getScopeConfig()
                ->getValue(self::STOCK_PATH . $id . '_' . $index . '_' . self::PRIORITY);
            $data[$index][self::IS_ACTIVE] = $this->getScopeConfig()
                ->getValue(self::STOCK_PATH . $id . '_' . $index . '_' . self::IS_ACTIVE);
        }

        return $data;
    }
}
