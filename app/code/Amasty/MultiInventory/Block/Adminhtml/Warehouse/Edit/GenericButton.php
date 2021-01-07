<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Warehouse\Edit;

use Amasty\MultiInventory\Api\Data\WarehouseInterface;
use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Amasty\MultiInventory\Model\WarehouseFactory as Factory;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Factory
     */
    protected $warehouseFactory;

    /**
     * @var WarehouseRepositoryInterface
     */
    public $repository;

    /**
     * GenericButton constructor.
     *
     * @param Context $context
     * @param Factory $warehouseFactory
     * @param WarehouseRepositoryInterface $repository
     */
    public function __construct(
        Context $context,
        Factory $warehouseFactory,
        WarehouseRepositoryInterface $repository
    ) {
        $this->context = $context;
        $this->warehouseFactory = $warehouseFactory;
        $this->repository = $repository;
    }

    /**
     * @return WarehouseInterface|null
     * @throws LocalizedException
     */
    public function getWarehouse()
    {
        if ($this->context->getRequest()->getParam('warehouse_id')) {
            try {
                return $this->repository->getById($this->context->getRequest()->getParam('warehouse_id'));
            } catch (NoSuchEntityException $e) {
                throw new LocalizedException(__('Can\'t get warehouse'), $e);
            }
        }

        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
