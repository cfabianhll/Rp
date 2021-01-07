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
use \Mirasvit\Rma\Model\Attachment;

/**
 * Select/insert/update of RMA items in DB
 */
class AttachmentRepository implements \Mirasvit\Rma\Api\Repository\AttachmentRepositoryInterface
{
    use \Mirasvit\Rma\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rma\Repository\RepositoryFunction\GetList;

    /**
     * @var Attachment[]
     */
    protected $instances = [];
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Attachment
     */
    private $itemResource;
    /**
     * @var \Mirasvit\Rma\Model\AttachmentFactory
     */
    private $objectFactory;
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Attachment\CollectionFactory
     */
    private $itemCollectionFactory;
    /**
     * @var \Mirasvit\Rma\Api\Data\RmaSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * AttachmentRepository constructor.
     * @param \Mirasvit\Rma\Model\AttachmentFactory $objectFactory
     * @param \Mirasvit\Rma\Model\ResourceModel\Attachment $itemResource
     * @param \Mirasvit\Rma\Model\ResourceModel\Attachment\CollectionFactory $itemCollectionFactory
     * @param \Mirasvit\Rma\Api\Data\RmaSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Mirasvit\Rma\Model\AttachmentFactory $objectFactory,
        \Mirasvit\Rma\Model\ResourceModel\Attachment $itemResource,
        \Mirasvit\Rma\Model\ResourceModel\Attachment\CollectionFactory $itemCollectionFactory,
        \Mirasvit\Rma\Api\Data\RmaSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->itemResource = $itemResource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Mirasvit\Rma\Api\Data\AttachmentInterface $item)
    {
        $this->itemResource->save($item);
        return $item;
    }

    /**
     * @param int $itemId
     * @param null $storeId
     * @return \Mirasvit\Rma\Api\Data\AttachmentInterface
     * @throws NoSuchEntityException
     */
    public function get($itemId, $storeId = null)
    {
        $cacheKey = null !== $storeId ? $storeId : 'all';
        if (!isset($this->instances[$itemId][$cacheKey])) {
            /** @var Attachment $item */
            $item = $this->objectFactory->create();
            if (null !== $storeId) {
                $item->setStoreId($storeId);
            }
            $item->load($itemId);
            if (!$item->getId()) {
                throw NoSuchEntityException::singleField('id', $itemId);
            }
            $this->instances[$itemId][$cacheKey] = $item;
        }
        return $this->instances[$itemId][$cacheKey];
    }

    /**
     * {@inheritdoc}
     */
    public function getByUid($uid)
    {
        if (!isset($this->instances[$uid])) {
            /** @var Attachment $attachment */
            $attachment = $this->objectFactory->create()->getCollection()
                ->addFieldToFilter('uid', $uid)
                ->getFirstItem();

            if (!$attachment->getId()) {
                throw NoSuchEntityException::singleField('uid', $uid);
            }
            $this->instances[$uid] = $attachment;
        }
        return $this->instances[$uid];
    }
    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rma\Api\Data\AttachmentInterface $item)
    {
        try {
            $itemId = $item->getId();
            $this->itemResource->delete($item);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete item with id %1',
                    $item->getId()
                ),
                $e
            );
        }
        unset($this->instances[$itemId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($itemId)
    {
        $item = $this->get($itemId);
        return  $this->delete($item);
    }

    /**
     * Validate item process
     *
     * @param  Attachment $item
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function validateAttachment(Attachment $item)
    {

    }
}
