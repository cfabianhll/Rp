<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer;

use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\Export\ConvertToCsv;
use Amasty\MultiInventory\Model\Indexer\Warehouse\Processor;
use Amasty\MultiInventory\Model\Warehouse\ItemFactory;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Config\Model\Config\Factory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\Timezone;

/**
 * Class ConfigObserver
 */
class ConfigObserver implements ObserverInterface
{
    /**
     * @var Processor
     */
    private $stockProcessor;

    /**
     * @var ConvertToCsv
     */
    private $converter;

    /**
     * @var ItemFactory
     */
    private $factory;

    /**
     * @var WarehouseFactory
     */
    private $whFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Factory
     */
    private $configFactory;

    private $cacheField;

    /**
     * @var Timezone
     */
    private $timezone;

    /**
     * ConfigObserver constructor.
     *
     * @param Processor $stockProcessor
     * @param ConvertToCsv $converter
     * @param ItemFactory $factory
     * @param WarehouseFactory $whFactory
     * @param Factory $configFactory
     * @param ManagerInterface $messageManager
     * @param Timezone $timezone
     */
    public function __construct(
        Processor $stockProcessor,
        ConvertToCsv $converter,
        ItemFactory $factory,
        WarehouseFactory $whFactory,
        Factory $configFactory,
        ManagerInterface $messageManager,
        Timezone $timezone
    ) {
        $this->stockProcessor = $stockProcessor;
        $this->converter = $converter;
        $this->factory = $factory;
        $this->whFactory = $whFactory;
        $this->messageManager = $messageManager;
        $this->configFactory = $configFactory;
        $this->timezone = $timezone;
    }

    /**
     * @param EventObserver $observer
     *
     * @return $this
     * @throws \Exception
     */
    public function execute(EventObserver $observer)
    {
        $object = $observer->getEvent()->getDataObject();

        $this->changeEnableMulti($object);
        $this->changeDecreaseStock($object);

        return $this;
    }

    /**
     * @param $object
     */
    private function changeEnableMulti($object)
    {
        if ($object->getPath() == System::CONFIG_ENABLE_MULTI
            && $object->isValueChanged()
        ) {
            if ($object->getValue()) {
                $this->whFactory->create()
                    ->getResource()
                    ->setDatafromInventory($this->whFactory->create()->getDefaultId());
            } else {
                try {
                    $name = $this->timezone->date()->format('Y_m_d_H_i_s');
                    $filename = 'export_' . $name . '.csv';
                    $collection = $this->factory->create()->getResource()->getItemsExport();
                    $file = $this->converter->getFileFromCollection($filename, $collection);
                    $this->messageManager->addSuccessMessage(__('Export Inventory to ') . $file['value']);
                    $this->factory->create()->getResource()->deleteItems();
                    $this->whFactory->create()->getResource()->updateManages();
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Export not work.'));
                }
            }
        }
    }

    /**
     * @param $object
     *
     * @throws \Exception
     */
    private function changeDecreaseStock($object)
    {
        $pathes = [
            System::CONFIG_DECREASE_STOCK,
            Configuration::XML_PATH_CAN_SUBTRACT
        ];

        if (in_array($object->getPath(), $pathes) && !$this->getCacheField()) {
            $pathDiff = array_diff($pathes, [$object->getPath()]);
            $path = null;
            foreach ($pathDiff as $value) {
                $path = $value;
            }
            $this->setCacheField($path);
            $options = explode("/", $path);

            $section = $options[0];
            $website = null;
            $store = null;
            $groups = [
                $options[1] => [
                        'fields' => [
                            $options[2] => [
                                'value' => $object->getValue()
                            ]
                        ]
                    ]
            ];
            $configData = [
                'section' => $section,
                'website' => $website,
                'store' => $store,
                'groups' => $groups,
            ];
            $configModel = $this->configFactory->create(['data' => $configData]);
            $configModel->save();
        }
    }

    /**
     * @return mixed
     */
    public function getCacheField()
    {
        return $this->cacheField;
    }

    /**
     * @param $path
     */
    public function setCacheField($path)
    {
        $this->cacheField = $path;
    }
}
