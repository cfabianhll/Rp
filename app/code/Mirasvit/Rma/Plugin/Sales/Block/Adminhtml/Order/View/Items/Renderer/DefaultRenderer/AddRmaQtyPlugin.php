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


namespace Mirasvit\Rma\Plugin\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer;

class AddRmaQtyPlugin
{
    /**
     * @var array
     */
    private $items = [];
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface
     */
    private $itemQtyService;
    /**
     * @var \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * AddRmaQtyPlugin constructor.
     * @param \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $itemRepository
     * @param \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface $itemQtyService
     */
    public function __construct(
        \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $itemRepository,
        \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface $itemQtyService
    ) {
        $this->itemRepository = $itemRepository;
        $this->itemQtyService = $itemQtyService;
    }

    /**
     * @param DefaultRenderer $renderer
     * @param \callable       $proceed
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetItem(DefaultRenderer $renderer, $proceed)
    {
        $item = $proceed();

        if ($item && !$item->getParentItem() && !in_array($item->getId(), $this->items)) {
            try {
                $rmaItem = $this->itemRepository->getByOrderItemId($item->getId());
                $item->setName($item->getName() . $this->getRmaItemQtyHtml($rmaItem));
                $this->items[] = $item->getId();
            } catch (NoSuchEntityException $e) {}
        }

        return $item;
    }

    /**
     * @param DefaultRenderer $renderer
     * @param \callable       $proceed
     * @param object          $item
     * @return mixed
     */
    public function aroundGetSelectionAttributes(DefaultRenderer $renderer, $proceed, $item)
    {
        $attr = $proceed($item);

        if ($attr) {
            try {
                $rmaItem = $this->itemRepository->getByOrderItemId($renderer->getPriceDataObject()->getId());
                $attr['option_label'] .= $this->getRmaItemQtyHtml($rmaItem);
            } catch (NoSuchEntityException $e) {}
        }

        return $attr;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $rmaItem
     * @return string
     */
    private function getRmaItemQtyHtml($rmaItem)
    {
        $qty = $this->itemQtyService->getItemQtyReturned($rmaItem);
        return $qty > 0 ? ' (RMA: ' . $qty . ')' : '';
    }
}