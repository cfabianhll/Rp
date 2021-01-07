<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Config\Source;

use Amasty\MultiInventory\Traits\ConfigOptions;

/**
 * Class Backorders
 */
class Backorders extends \Magento\CatalogInventory\Model\Source\Backorders
{
    use ConfigOptions;

    const USE_CONFIG_OPTION_VALUE = '-1';
}
