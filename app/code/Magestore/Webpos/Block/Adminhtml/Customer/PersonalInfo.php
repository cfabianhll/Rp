<?php

namespace Magestore\Webpos\Block\Adminhtml\Customer;

use Braintree\Exception;
use Magento\Backend\Block\Template\Context;
use Magestore\Webpos\Api\Customer\CustomerRepositoryInterface;
use Magestore\Webpos\Api\Data\Customer\CustomerInterface;
use Magestore\Webpos\Api\Location\LocationRepositoryInterface;

/**
 * Additional Class PersonalInfo
 */
class PersonalInfo extends \Magento\Backend\Block\Template
{
    /**
     * @var LocationRepositoryInterface
     */
    protected $_locationRepositoryInterface;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * Constructor for location attribute source
     *
     * @param Context $context
     * @param LocationRepositoryInterface $locationRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        LocationRepositoryInterface $locationRepository,
        CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->_locationRepositoryInterface = $locationRepository;
        $this->_customerRepository = $customerRepository;

        parent::__construct($context, $data);
    }

    /**
     * Get Customer's created location name
     *
     * @return string|null
     */
    public function getCreatedLocationName()
    {
        $customerId = $this->getParentBlock()->getCustomer()->getId();
        $customer = $this->_customerRepository->getById($customerId);
        $locationId = $customer->getCreatedLocationId();
        if ($locationId === null) {
            return null;
        }

        $dataLocation = null;
        try {
            $dataLocation = $this->_locationRepositoryInterface->getById($locationId);
        } catch (\Exception $error) {
            $dataLocation = null;
        }

        if ($dataLocation) {
            return $dataLocation->getName();
        }
        return null;
    }
}
