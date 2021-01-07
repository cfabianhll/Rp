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



namespace Mirasvit\Rma\Model\UI\Rma\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class OrderColumn extends Column
{
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface
     */
    private $rmaOrderService;
    /**
     * @var \Mirasvit\Rma\Model\RmaFactory
     */
    private $rmaFactory;
    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;
    /**
     * @var \Magento\Backend\Helper\Data
     */
    private $helper;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface $rmaOrderService
     * @param \Mirasvit\Rma\Model\RmaFactory $rmaFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Backend\Helper\Data $helper
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface $rmaOrderService,
        \Mirasvit\Rma\Model\RmaFactory $rmaFactory,
        \Magento\Framework\Escaper $escaper,
        \Magento\Backend\Helper\Data $helper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->rmaOrderService = $rmaOrderService;
        $this->rmaFactory      = $rmaFactory;
        $this->escaper         = $escaper;
        $this->helper          = $helper;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');

                $str = '';
                if ($name == 'exchange_order_ids') {
                    $rma = $this->rmaFactory->create();
                    $rma->getResource()->load($rma, $item[$rma->getIdFieldName()]);
                    $rma->getResource()->afterLoad($rma);
                    $orderIds = (array)$rma->getExchangeOrderIds();
                    $orderIncrements = (array)$rma->getExchangeOrderIncrements();
                    $str = '';
                    foreach ($orderIds as $id) {
                        try {
                            $order = $this->orderRepository->get($id);
                            $str .= $order->getIncrementId();
                        } catch (\Exception $e) {
                            $str .= __('Exchange order was removed #%1', $orderIncrements[$id])->render();
                        }
                        $str .= ', ';
                    }
                    $str = trim($str, ', ');
                }
                if ($name == 'order_id') {
                    $rma = $this->rmaFactory->create();
                    $rma->getResource()->load($rma, $item[$rma->getIdFieldName()]);
                    $rma->getResource()->afterLoad($rma);
                    $str = '';
                    $orders = $this->rmaOrderService->getOrders($rma);
                    if (!count($orders)) {
                        $str .= __('Removed Order')->render();
                    }
                    foreach ($orders as $order) {
                        if ($order) {
                            $str .= '#';
                            if ($order->getIsOffline()) {
                                $str .= $this->escaper->escapeHtml($order->getReceiptNumber());
                            } else {
                                $str .= $order->getIncrementId();
                            }
                            $str .= '<br>';
                        } else {
                            $str .= __('Removed Order')->render();
                        }
                    }
                }
                $item[$name] = $str;
            }
        }

        return $dataSource;
    }

    /**
     * @param string $id
     */
    private function getExchangeOrder($id)
    {

    }
}