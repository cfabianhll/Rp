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
use Mirasvit\Rma\Model\Field;

class FieldRepository implements \Mirasvit\Rma\Api\Repository\FieldRepositoryInterface
{
    use \Mirasvit\Rma\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rma\Repository\RepositoryFunction\GetList;

    /**
     * @var Field[]
     */
    protected $instances = [];
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Field\CollectionFactory
     */
    private $fieldCollectionFactory;
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Field
     */
    private $fieldResource;
    /**
     * @var \Mirasvit\Rma\Model\FieldFactory
     */
    private $objectFactory;
    /**
     * @var \Mirasvit\Rma\Api\Data\FieldSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * FieldRepository constructor.
     * @param \Mirasvit\Rma\Model\ResourceModel\Field\CollectionFactory $fieldCollectionFactory
     * @param \Mirasvit\Rma\Model\FieldFactory $objectFactory
     * @param \Mirasvit\Rma\Model\ResourceModel\Field $fieldResource
     * @param \Mirasvit\Rma\Api\Data\FieldSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Mirasvit\Rma\Model\ResourceModel\Field\CollectionFactory $fieldCollectionFactory,
        \Mirasvit\Rma\Model\FieldFactory $objectFactory,
        \Mirasvit\Rma\Model\ResourceModel\Field $fieldResource,
        \Mirasvit\Rma\Api\Data\FieldSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->fieldCollectionFactory = $fieldCollectionFactory;
        $this->objectFactory          = $objectFactory;
        $this->fieldResource          = $fieldResource;
        $this->searchResultsFactory   = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Mirasvit\Rma\Api\Data\FieldInterface $field)
    {
        $this->fieldResource->save($field);

        return $field;
    }

    /**
     * {@inheritdoc}
     */
    public function get($fieldId)
    {
        if (!isset($this->instances[$fieldId])) {
            /** @var Field $field */
            $field = $this->objectFactory->create();
            $field->load($fieldId);
            if (!$field->getId()) {
                throw NoSuchEntityException::singleField('id', $fieldId);
            }
            $this->instances[$fieldId] = $field;
        }

        return $this->instances[$fieldId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rma\Api\Data\FieldInterface $field)
    {
        try {
            $fieldId = $field->getId();
            $this->fieldResource->delete($field);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete field with id %1',
                    $field->getId()
                ),
                $e
            );
        }
        unset($this->instances[$fieldId]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($fieldId)
    {
        $field = $this->get($fieldId);

        return  $this->delete($field);
    }

    /**
     * Validate field process
     *
     * @param  Field $field
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function validateField(Field $field)
    {

    }

    /**
     * @return \Mirasvit\Rma\Model\ResourceModel\Field\Collection
     */
    public function getCollection()
    {
        return $this->fieldCollectionFactory->create();
    }
}
