<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\WebposCustomerBugfix\Plugin\Model\Integration;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DataObject;

/**
 * Class LoyaltyRepositoryPlugin
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoyaltyRepositoryPlugin
{
    /**
     * @var \Magestore\Webpos\Api\Data\Sales\OrderSearchResultInterfaceFactory
     */
    protected $searchResultsFactory;
    /**
     * @var \Magestore\Webpos\Model\Customer\CustomerRepository
     */
    protected $customerRepository;
    /**
     * @var \Magestore\WebposCustomerBugfix\Helper\Data
     */
    protected $webposCustomerBugfixHelper;
    /**
     * @var \Magestore\Webpos\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * LoyaltyRepositoryPlugin constructor.
     * @param \Magestore\Webpos\Model\Customer\CustomerRepository                $customerRepository
     * @param \Magestore\Webpos\Api\Data\Sales\OrderSearchResultInterfaceFactory $searchResultsFactory
     * @param \Magestore\WebposCustomerBugfix\Helper\Data                        $webposCustomerBugfixHelper
     * @param \Magestore\Webpos\Helper\Data                                      $helper
     * @param \Magento\Framework\Event\ManagerInterface                          $eventManager
     */
    public function __construct(
        \Magestore\Webpos\Model\Customer\CustomerRepository $customerRepository,
        \Magestore\Webpos\Api\Data\Sales\OrderSearchResultInterfaceFactory $searchResultsFactory,
        \Magestore\WebposCustomerBugfix\Helper\Data $webposCustomerBugfixHelper,
        \Magestore\Webpos\Helper\Data $helper,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->webposCustomerBugfixHelper = $webposCustomerBugfixHelper;
        $this->helper = $helper;
        $this->eventManager = $eventManager;
    }
    /**
     * AroundGetList
     *
     * @param \Magestore\Webpos\Model\Integration\LoyaltyRepository $subject
     * @param \Closure                                              $proceed
     * @param SearchCriteriaInterface                               $searchCriteria
     * @return mixed
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetList(
        \Magestore\Webpos\Model\Integration\LoyaltyRepository $subject,
        \Closure $proceed,
        SearchCriteriaInterface $searchCriteria
    ) {
        $searchResults = $this->searchResultsFactory->create();
        /** @var Collection $collection */
        $collection = $this->customerRepository->getCustomerCollection($searchCriteria, true);
        $subject->getLoyaltyCollection($collection);
        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $searchResults->setTotalCount($collection->getSize());

        $customers = [];
        /** @var Customer $customerModel */
        foreach ($collection as $customerModel) {
            $customers[] = $this->webposCustomerBugfixHelper->convertCoreCustomerToWebposCustomer($customerModel);
        }
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($customers);
        return $searchResults;
    }
    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \Magento\Customer\Model\ResourceModel\Customer\Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Magento\Customer\Model\ResourceModel\Customer\Collection $collection
    ) {
        $checkProcess = new DataObject();
        $updatedAt = '';
        foreach ($filterGroup->getFilters() as $filter) {
            if ($filter->getField() == 'updated_at') {
                $updatedAt = $filter->getValue();
                break;
            }
        }
        $this->eventManager->dispatch(
            'loyalty_add_filter_group',
            ['collection' => $collection, 'updated_at' => $updatedAt, 'check_process' => $checkProcess]
        );
        if ($updatedAt && !$checkProcess->getData('process_store_credit')) {
            if ($this->helper->isStoreCreditEnable()) {
                $collection->getSelect()->where('customer_credit.updated_at >= "' . $updatedAt . '"');
            }
        }
    }
}
