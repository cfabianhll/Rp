<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\WebposCustomerBugfix\Helper;

use Magento\Framework\Api\DataObjectHelper;

/**
 * Class Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magestore\Webpos\Api\Data\Customer\CustomerInterfaceFactory
     */
    protected $customerDataFactory;
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Customer constructor.
     *
     * @param \Magento\Framework\App\Helper\Context                        $context
     * @param DataObjectHelper                                             $dataObjectHelper
     * @param \Magestore\Webpos\Api\Data\Customer\CustomerInterfaceFactory $customerDataFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        DataObjectHelper $dataObjectHelper,
        \Magestore\Webpos\Api\Data\Customer\CustomerInterfaceFactory $customerDataFactory
    ) {
        parent::__construct($context);
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerDataFactory = $customerDataFactory;
    }

    /**
     * Retrieve customer model with customer data
     *
     * @param \Magento\Customer\Model\Customer $model
     *
     * @return \Magestore\Webpos\Api\Data\Customer\CustomerInterface
     */
    public function convertCoreCustomerToWebposCustomer($model)
    {
        $customerData = $model->getData();
        $addressesData = [];
        /** @var \Magento\Customer\Model\Address $address */
        foreach ($model->getAddresses() as $address) {
            $addressesData[] = $address->getDataModel();
        }
        $customerDataObject = $this->customerDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerDataObject,
            $customerData,
            \Magestore\Webpos\Api\Data\Customer\CustomerInterface::class
        );
        $customerDataObject->setAddresses($addressesData)
                           ->setId($model->getId());
        return $customerDataObject;
    }
}
