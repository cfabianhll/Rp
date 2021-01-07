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


namespace Mirasvit\Rma\Repository;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Mirasvit\Rma\Model\Rma;
use Mirasvit\Rma\Model\Address;

class AddressRepository implements \Mirasvit\Rma\Api\Repository\AddressRepositoryInterface
{
    use \Mirasvit\Rma\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rma\Repository\RepositoryFunction\GetList;

    /**
     * @var Address[]
     */
    protected $instances = [];
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Address
     */
    private $addressResource;
    /**
     * @var \Mirasvit\Rma\Model\AddressFactory
     */
    private $objectFactory;
    /**
     * @var \Mirasvit\Rma\Api\Data\AddressSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * AddressRepository constructor.
     * @param \Mirasvit\Rma\Model\AddressFactory $objectFactory
     * @param \Mirasvit\Rma\Model\ResourceModel\Address $addressResource
     * @param \Mirasvit\Rma\Api\Data\AddressSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Mirasvit\Rma\Model\AddressFactory $objectFactory,
        \Mirasvit\Rma\Model\ResourceModel\Address $addressResource,
        \Mirasvit\Rma\Api\Data\AddressSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->objectFactory        = $objectFactory;
        $this->addressResource      = $addressResource;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Mirasvit\Rma\Api\Data\AddressInterface $address)
    {
        $this->addressResource->save($address);

        return $address;
    }

    /**
     * {@inheritdoc}
     */
    public function get($addressId)
    {
        if (!isset($this->instances[$addressId])) {
            /** @var Address $address */
            $address = $this->objectFactory->create();
            $address->load($addressId);
            if (!$address->getId()) {
                throw NoSuchEntityException::singleField('id', $addressId);
            }
            $this->instances[$addressId] = $address;
        }
        return $this->instances[$addressId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rma\Api\Data\AddressInterface $address)
    {
        try {
            $addressId = $address->getId();
            $this->addressResource->delete($address);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete return address with id %1',
                    $address->getId()
                ),
                $e
            );
        }
        unset($this->instances[$addressId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($addressId)
    {
        $address = $this->get($addressId);

        return  $this->delete($address);
    }

    /**
     * Validate address process
     *
     * @param  Address $address
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function validateAddress(Address $address)
    {

    }
}
