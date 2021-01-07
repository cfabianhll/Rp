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
use Mirasvit\Rma\Helper\Serializer;

class ItemsColumn extends Column
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
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface
     */
    private $rmaSearchManagement;
    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;
    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * ItemsColumn constructor.
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface $rmaOrderService
     * @param Serializer $serializer
     * @param \Mirasvit\Rma\Model\RmaFactory $rmaFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface $rmaOrderService,
        Serializer $serializer,
        \Mirasvit\Rma\Model\RmaFactory $rmaFactory,
        \Magento\Framework\Escaper $escaper,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->rmaOrderService = $rmaOrderService;
        $this->rmaFactory = $rmaFactory;
        $this->rmaSearchManagement = $rmaSearchManagement;
        $this->escaper = $escaper;
        $this->orderItemRepository = $orderItemRepository;
        $this->serializer = $serializer;
    }

    /**
     * @param string $value
     * @return string
     * @throws \Zend_Json_Exception
     */
    private function getLocalizedValue($value)
    {
        if ($serialized = $this->serializer->unserialize($value)) {
            return array_values($serialized)[0];
        }
        return $value;
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

                $rma = $this->rmaFactory->create();
                $rma->getResource()->load($rma, $item['rma_id']);

                $s = '';
                $orders = $this->rmaOrderService->getOrders($rma);
                if ($orders) {
                    $s .= $this->renderItems($this->rmaSearchManagement->getRequestedOfflineItems($rma));
                    $s .= $this->renderItems($this->rmaSearchManagement->getRequestedItems($rma));
                }

                $item[$name] = $s;
            }
        }

        return $dataSource;
    }

    /**
     * @param array $items Array of objects
     * @return string
     * @throws \Zend_Json_Exception
     */
    private function renderItems($items)
    {
        $s = '';
        foreach ($items as $currentItem) {
            if ($currentItem->getIsOffline()) {
                $orderItem = $currentItem;
            } else {
                $orderItem = $this->orderItemRepository->get($currentItem->getOrderItemId());
            }

            $s .= '<b>' . $this->escaper->escapeHtml($orderItem->getName()) . '</b>';
            $s .= ' / ';
            $s .= $currentItem->getReasonName() ?
                $this->escaper->escapeHtml($this->getLocalizedValue($currentItem->getReasonName())) : '-';
            $s .= ' /  ';
            $s .= $currentItem->getConditionName() ?
                $this->escaper->escapeHtml($this->getLocalizedValue($currentItem->getConditionName())) : '-';
            $s .= ' / ';
            $s .= $currentItem->getResolutionName() ?
                $this->escaper->escapeHtml($this->getLocalizedValue($currentItem->getResolutionName())) : '-';
            $s .= '<br>';
        }

        return $s;
    }
}