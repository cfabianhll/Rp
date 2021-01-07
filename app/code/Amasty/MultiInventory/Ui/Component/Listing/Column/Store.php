<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column;

use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Class Store
 */
class Store extends \Magento\Store\Ui\Component\Listing\Column\Store
{
    /**
     * @var WarehouseFactory
     */
    private $factory;

    /**
     * @var WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * Store constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SystemStore $systemStore
     * @param Escaper $escaper
     * @param WarehouseFactory $factory
     * @param WarehouseRepositoryInterface $repository
     * @param array|null $components
     * @param array|null $data
     * @param string $storeKey
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SystemStore $systemStore,
        Escaper $escaper,
        WarehouseFactory $factory,
        WarehouseRepositoryInterface $repository,
        array $components = null,
        array $data = null,
        $storeKey = 'store_id'
    ) {
        parent::__construct($context, $uiComponentFactory, $systemStore, $escaper, $components, $data, $storeKey);
        $this->factory = $factory;
        $this->repository = $repository;
    }

    /**
     * Get data
     *
     * @param array $item
     *
     * @return string
     * @throws NoSuchEntityException
     */
    protected function prepareItem(array $item)
    {

        $content = '';
        $origStores = 0;
        $collection = $this->repository->getById($item['warehouse_id'])->getStores();
        if (count($collection)) {
            $origStores = [];
            foreach ($collection as $store) {
                $origStores[] = $store->getStoreId();
            }
        }
        if (!is_array($origStores)) {
            $origStores = [$origStores];
        }

        if (in_array(0, $origStores) && count($origStores) == 1) {
            return __('All Store Views');
        }

        $data = $this->systemStore->getStoresStructure(false, $origStores);

        foreach ($data as $website) {
            // @codingStandardsIgnoreLine
            $content .= $website['label'] . '<br/>';
            foreach ($website['children'] as $group) {
                // @codingStandardsIgnoreLine;
                $content .= str_repeat('&nbsp;', 3) . $this->escaper->escapeHtml($group['label']) . '<br/>';
                foreach ($group['children'] as $store) {
                    // @codingStandardsIgnoreLine;
                    $content .= str_repeat('&nbsp;', 6) . $this->escaper->escapeHtml($store['label']) . '<br/>';
                }
            }
        }

        return $content;
    }
}
