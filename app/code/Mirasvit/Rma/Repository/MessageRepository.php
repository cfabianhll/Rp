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

class MessageRepository implements \Mirasvit\Rma\Api\Repository\MessageRepositoryInterface
{
    use \Mirasvit\Rma\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rma\Repository\RepositoryFunction\GetList;

    /**
     * @var \Mirasvit\Rma\Api\Data\MessageInterface[]
     */
    protected $instances = [];
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Message\CollectionFactory
     */
    private $messageCollectionFactory;
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Message
     */
    private $messageResource;
    /**
     * @var \Mirasvit\Rma\Model\MessageFactory
     */
    private $objectFactory;
    /**
     * @var \Mirasvit\Rma\Api\Data\MessageSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * MessageRepository constructor.
     * @param \Mirasvit\Rma\Model\MessageFactory $objectFactory
     * @param \Mirasvit\Rma\Model\ResourceModel\Message $messageResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Mirasvit\Rma\Api\Data\MessageSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Mirasvit\Rma\Model\ResourceModel\Message\CollectionFactory $messageCollectionFactory
     */
    public function __construct(
        \Mirasvit\Rma\Model\MessageFactory $objectFactory,
        \Mirasvit\Rma\Model\ResourceModel\Message $messageResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mirasvit\Rma\Api\Data\MessageSearchResultsInterfaceFactory $searchResultsFactory,
        \Mirasvit\Rma\Model\ResourceModel\Message\CollectionFactory $messageCollectionFactory
    ) {
        $this->objectFactory            = $objectFactory;
        $this->messageResource          = $messageResource;
        $this->storeManager             = $storeManager;
        $this->searchResultsFactory     = $searchResultsFactory;
        $this->messageCollectionFactory = $messageCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Mirasvit\Rma\Api\Data\MessageInterface $message)
    {
        $this->messageResource->save($message);
        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function get($messageId)
    {
        if (!isset($this->instances[$messageId])) {
            /** @var \Mirasvit\Rma\Model\Message $message */
            $message = $this->objectFactory->create();
            $message->load($messageId);
            if (!$message->getId()) {
                throw NoSuchEntityException::singleField('id', $messageId);
            }
            $this->instances[$messageId] = $message;
        }
        return $this->instances[$messageId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rma\Api\Data\MessageInterface $message)
    {
        try {
            $messageId = $message->getId();
            $this->messageResource->delete($message);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete message with id %1',
                    $message->getId()
                ),
                $e
            );
        }
        unset($this->instances[$messageId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($messageId)
    {
        $message = $this->get($messageId);
        return  $this->delete($message);
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->messageCollectionFactory->create();
    }
}
