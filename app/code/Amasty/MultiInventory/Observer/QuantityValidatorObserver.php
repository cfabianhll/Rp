<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer;

use Amasty\MultiInventory\Model\Warehouse\Item\QuantityValidator;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class QuantityValidatorObserver
 */
class QuantityValidatorObserver implements ObserverInterface
{
    /**
     * @var QuantityValidator
     */
    protected $quantityValidator;

    public function __construct(
        QuantityValidator $quantityValidator
    ) {
        $this->quantityValidator = $quantityValidator;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->quantityValidator->validate($observer);
    }
}
