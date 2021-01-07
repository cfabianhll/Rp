<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Bundle\Model\ResourceModel\Option;

use Amasty\MultiInventory\Helper\System;

/**
 * Class Collection
 */
class Collection
{
    /**
     * @var System
     */
    private $system;

    public function __construct(System $system)
    {
        $this->system = $system;
    }
    /**
     * @param \Magento\Bundle\Model\ResourceModel\Option\Collection $model
     * @param \Closure $work
     * @param $selectionsCollection
     * @param bool $stripBefore
     * @param bool $appendAll
     * @return mixed
     */
    public function aroundAppendSelections(
        \Magento\Bundle\Model\ResourceModel\Option\Collection $model,
        \Closure $work,
        $selectionsCollection,
        $stripBefore = false,
        $appendAll = true
    ) {
        if ($this->system->isMultiEnabled()) {
            $stripBefore = true;
        }

        return $work($selectionsCollection, $stripBefore, $appendAll);
    }
}
