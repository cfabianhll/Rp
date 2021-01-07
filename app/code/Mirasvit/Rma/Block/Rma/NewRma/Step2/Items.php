<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.1.12
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Block\Rma\NewRma\Step2;

class Items extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mirasvit\Rma\Service\Item\ItemListBuilder
     */
    private $itemListBuilder;
    /**
     * @var \Mirasvit\Rma\Helper\Controller\Rma\AbstractStrategy
     */
    private $strategy;
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;

    /**
     * Items constructor.
     * @param \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory
     * @param \Mirasvit\Rma\Service\Item\ItemListBuilder $itemListBuilder
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory,
        \Mirasvit\Rma\Service\Item\ItemListBuilder $itemListBuilder,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->strategy        = $strategyFactory->create();
        $this->itemListBuilder = $itemListBuilder;
        $this->context         = $context;

        parent::__construct($context, $data);
    }

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (!$this->order) {
            if ($orderId = $this->context->getRequest()->getParam('order_id')) {
                $items = $this->strategy->getAllowedOrderList();
                if (isset($items[$orderId])) {
                    $this->order = $items[$orderId];
                }
            }
        }
        return $this->order;
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    public function getRmaItems()
    {
        return $this->itemListBuilder->getList($this->getOrder());
    }
}