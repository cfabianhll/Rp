<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Plugin\Order\Grid;

/**
 * Order Grid Collection Plugin
 */
class Collection
{
    /**
     * @var \Magestore\Webpos\Model\Source\Adminhtml\Location
     */
    protected $locationSource;

    protected $locationSourceOptions;

    /**
     * @var \Magestore\Webpos\Model\Source\Adminhtml\Staff
     */
    protected $staffSource;

    protected $staffOptions;

    /**
     * Collection construct
     *
     * @param \Magestore\Webpos\Model\Source\Adminhtml\Location $locationSource
     * @param \Magestore\Webpos\Model\Source\Adminhtml\Staff $staffSource
     */
    public function __construct(
        \Magestore\Webpos\Model\Source\Adminhtml\Location $locationSource,
        \Magestore\Webpos\Model\Source\Adminhtml\Staff $staffSource
    ) {
        $this->locationSource = $locationSource;
        $this->staffSource = $staffSource;
    }

    /**
     * After Get Data
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(
        \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $subject,
        $result
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $requestInterface = $objectManager->get(\Magento\Framework\App\RequestInterface::class);
        $metadataProvider = $objectManager->get(\Magento\Ui\Model\Export\MetadataProvider::class);
        if (!method_exists($metadataProvider, 'getColumnOptions')) {
            if (($requestInterface->getActionName() == 'gridToCsv')
                || ($requestInterface->getActionName() == 'gridToXml')
            ) {
                $options = $this->getLocationSourceOptions();
                $optionsFulfill = ['0'=> 'No','1'=>'Yes'];
                $optionsStaff = $this->getStaffOptions();
                foreach ($result as &$item) {
                    if ($item['pos_location_id']) {
                        $item['pos_location_id'] = $options[$item['pos_location_id']];
                    }
                    if (isset($item['pos_fulfill_online'])) {
                        $item['pos_fulfill_online'] = $optionsFulfill[$item['pos_fulfill_online']];
                    }
                    if (isset($item['pos_staff_id'])) {
                        $item['pos_staff_id'] = $optionsStaff[$item['pos_staff_id']];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get Location Source Options
     *
     * @return array
     */
    public function getLocationSourceOptions()
    {
        if (!$this->locationSourceOptions) {
            $this->locationSourceOptions = $this->locationSource->getOptionArray();
        }
        return $this->locationSourceOptions;
    }

    /**
     * Get Staff Options
     *
     * @return array
     */
    public function getStaffOptions()
    {
        if (!$this->staffOptions) {
            $this->staffOptions = $this->staffSource->getOptionArray();
        }
        return $this->staffOptions;
    }
}
