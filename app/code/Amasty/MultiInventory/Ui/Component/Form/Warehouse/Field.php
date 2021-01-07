<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Form\Warehouse;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Field
 */
class Field extends \Magento\Ui\Component\Form\Field
{
    /**
     * Prepare component configuration
     *
     * @return void
     * @throws LocalizedException
     */
    public function prepare()
    {
        if ($model = $this->getDataByKey('default')) {
            $defaults = $model->toOptionArray();
            $config = $this->getData('config');
            $config['default'] = implode(",", $defaults);
            $this->setData('config', $config);
        }

        parent::prepare();
    }
}
