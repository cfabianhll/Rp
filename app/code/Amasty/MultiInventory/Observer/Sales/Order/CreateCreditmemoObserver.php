<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer\Sales\Order;

use Amasty\MultiInventory\Helper\Data;
use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Order\Item\CollectionFactory;
use Amasty\MultiInventory\Model\Warehouse\Order\Item;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order\Creditmemo;

/**
 * Class CreateCreditmemoObserver
 */
class CreateCreditmemoObserver extends CreateAbstractObserver
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * CreateCreditmemoObserver constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param Data $helper
     * @param System $system
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Data $helper,
        System $system,
        ManagerInterface $messageManager,
        RequestInterface $request
    ) {
        parent::__construct($collectionFactory, $helper, $system);
        $this->messageManager = $messageManager;
        $this->request = $request;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if ($this->isCanExecute()) {
            parent::execute($observer);
        } else {
            $this->messageManager->addNoticeMessage(
                __('The "Return Credit Memo Item to Stock" setting is disabled. '
                    . 'The returned item(s) do not affect the stock.')
            );
        }
    }

    /**
     * @param Item $item
     * @param Creditmemo $entity
     */
    protected function processItem($item, $entity)
    {
        $orderItemId = $item->getOrderItemId();
        $params = $this->request->getParams();
        if (isset($params['creditmemo']['items'][$orderItemId]['back_to_stock'])
            && $params['creditmemo']['items'][$orderItemId]['back_to_stock']
        ) {
            $this->helper->setReturn($item, $entity);
            $this->messageManager->addNoticeMessage(
                __(
                    'The returned item(s) affected the product quantity in the appropriate Warehouse %1.',
                    $item->getWarehouse()->getTitle()
                )
            );
        }
    }

    /**
     * @return bool
     */
    protected function isCanExecute()
    {
        return parent::isCanExecute() && $this->system->getReturnCreditmemo();
    }
}
