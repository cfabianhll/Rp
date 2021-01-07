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


namespace Mirasvit\Rma\Model\ResourceModel;

use Magento\Framework\Exception\NoSuchEntityException;
use Mirasvit\Helpdesk\Api\Data\StatusInterface;

class Rma extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Catalog\Model\Product\Url
     */
    private $productUrl;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var null
     */
    private $resourcePrefix;
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Context
     */
    private $context;

    /**
     * Rma constructor.
     * @param \Magento\Catalog\Model\Product\Url $productUrl
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param null $resourcePrefix
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Url $productUrl,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null
    ) {
        $this->productUrl      = $productUrl;
        $this->orderRepository = $orderRepository;
        $this->context         = $context;
        $this->resourcePrefix  = $resourcePrefix;

        parent::__construct($context, $resourcePrefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('mst_rma_rma', 'rma_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return \Magento\Framework\Model\AbstractModel|\Mirasvit\Rma\Model\Rma
     */
    protected function loadStoreIds(\Magento\Framework\Model\AbstractModel $object)
    {
        /* @var  \Mirasvit\Rma\Model\Rma $object */
        $select = $this->getConnection()->select()
            ->from($this->getTable('mst_rma_rma_store'))
            ->where('rs_rma_id = ?', $object->getId());
        if ($data = $this->getConnection()->fetchAll($select)) {
            $array = [];
            foreach ($data as $row) {
                $array[] = $row['rs_store_id'];
            }
            $object->setData('store_ids', $array);
        }

        return $object;
    }

    /**
     * @param \Mirasvit\Rma\Model\Rma $object
     * @return void
     */
    protected function saveStoreIds($object)
    {
        /* @var  \Mirasvit\Rma\Model\Rma $object */
        $condition = $this->getConnection()->quoteInto('rs_rma_id = ?', $object->getId());
        $this->getConnection()->delete($this->getTable('mst_rma_rma_store'), $condition);
        foreach ((array) $object->getData('store_ids') as $id) {
            $objArray = [
                'rs_rma_id' => $object->getId(),
                'rs_store_id' => $id,
            ];
            $this->getConnection()->insert(
                $this->getTable('mst_rma_rma_store'),
                $objArray
            );
        }
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return \Magento\Framework\Model\AbstractModel|\Mirasvit\Rma\Model\Rma
     */
    protected function loadExchangeOrderIds(\Magento\Framework\Model\AbstractModel $object)
    {
        /* @var  \Mirasvit\Rma\Model\Rma $object */
        $select = $this->getConnection()->select()
            ->from($this->getTable('mst_rma_rma_order'))
            ->where('re_rma_id = ?', $object->getId());
        if ($data = $this->getConnection()->fetchAll($select)) {
            $array = [];
            $increments = [];
            foreach ($data as $row) {
                $array[] = $row['re_exchange_order_id'];
                $increments[$row['re_exchange_order_id']] = $row['re_exchange_order_increment'];
            }
            $object->setData('exchange_order_ids', $array);
            $object->setData('exchange_order_increments', $increments);
        }
        //for backward compatibility
        if ($object->getExchangeOrderIds() && count($object->getExchangeOrderIds()) == 0 && $object->getExchangeOrderId()) {
            $object->setData('exchange_order_ids', [$object->getExchangeOrderId()]);
        }

        return $object;
    }

    /**
     * @param \Mirasvit\Rma\Model\Rma $object
     * @return void
     */
    protected function saveExchangeOrderIds($object)
    {
        /* @var  \Mirasvit\Rma\Model\Rma $object */
        $condition = $this->getConnection()->quoteInto('re_rma_id = ?', $object->getId());
        $this->getConnection()->delete($this->getTable('mst_rma_rma_order'), $condition);
        $increments = (array) $object->getData('exchange_order_increments');
        foreach ((array) $object->getData('exchange_order_ids') as $id) {
            try {
                $incrementId = $this->orderRepository->get($id)->getIncrementId();
            } catch (NoSuchEntityException $e) {
                // exchange order was removed
                $incrementId = empty($increments[$id]) ? $id : $increments[$id];
            }
            $objArray = [
                're_rma_id' => $object->getId(),
                're_exchange_order_id' => $id,
                're_exchange_order_increment' => $incrementId,
            ];
            $this->getConnection()->insert(
                $this->getTable('mst_rma_rma_order'),
                $objArray
            );
        }
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return \Magento\Framework\Model\AbstractModel|\Mirasvit\Rma\Model\Rma
     */
    protected function loadReplacementOrderIds(\Magento\Framework\Model\AbstractModel $object)
    {
        /* @var  \Mirasvit\Rma\Model\Rma $object */
        $select = $this->getConnection()->select()
            ->from($this->getTable('mst_rma_rma_replacement_order'))
            ->where('rma_id = ?', $object->getId());
        if ($data = $this->getConnection()->fetchAll($select)) {
            $array = [];
            $increments = [];
            foreach ($data as $row) {
                $array[] = $row['replacement_order_id'];
                $increments[$row['replacement_order_id']] = $row['replacement_order_increment'];
            }
            $object->setData('replacement_order_ids', $array);
            $object->setData('replacement_order_increments', $increments);
        }
        //for backward compatibility
        if ($object->getReplacementOrderIds() && count($object->getReplacementOrderIds()) == 0 && $object->getReplacementOrderId()) {
            $object->setData('replacement_order_ids', [$object->getReplacementOrderId()]);
        }

        return $object;
    }

    /**
     * @param \Mirasvit\Rma\Model\Rma $object
     * @return void
     */
    protected function saveReplacementOrderIds($object)
    {
        /* @var  \Mirasvit\Rma\Model\Rma $object */
        $condition = $this->getConnection()->quoteInto('rma_id = ?', $object->getId());
        $this->getConnection()->delete($this->getTable('mst_rma_rma_replacement_order'), $condition);
        $increments = (array) $object->getData('replacement_order_increments');
        foreach ((array) $object->getData('replacement_order_ids') as $id) {
            try {
                $incrementId = $this->orderRepository->get($id)->getIncrementId();
            } catch (NoSuchEntityException $e) {
                // exchange order was removed
                $incrementId = empty($increments[$id]) ? $id : $increments[$id];
            }
            $objArray = [
                'rma_id' => $object->getId(),
                'replacement_order_id' => $id,
                'replacement_order_increment' => $incrementId,
            ];
            $this->getConnection()->insert(
                $this->getTable('mst_rma_rma_replacement_order'),
                $objArray
            );
        }
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return \Magento\Framework\Model\AbstractModel|\Mirasvit\Rma\Model\Rma
     */
    protected function loadCreditMemoIds(\Magento\Framework\Model\AbstractModel $object)
    {
        /* @var  \Mirasvit\Rma\Model\Rma $object */
        $select = $this->getConnection()->select()
            ->from($this->getTable('mst_rma_rma_creditmemo'))
            ->where('rc_rma_id = ?', $object->getId());
        if ($data = $this->getConnection()->fetchAll($select)) {
            $array = [];
            foreach ($data as $row) {
                $array[] = $row['rc_credit_memo_id'];
            }
            $object->setData('credit_memo_ids', $array);
        }
        //for backward compatibility
        if ($object->getCreditMemoIds() && count($object->getCreditMemoIds()) == 0 && $object->getCreditMemoId()) {
            $object->setData('credit_memo_ids', [$object->getCreditMemoId()]);
        }

        return $object;
    }

    /**
     * @param \Mirasvit\Rma\Model\Rma $object
     * @return void
     */
    protected function saveCreditMemoIds($object)
    {
        /* @var  \Mirasvit\Rma\Model\Rma $object */
        $condition = $this->getConnection()->quoteInto('rc_rma_id = ?', $object->getId());
        $this->getConnection()->delete($this->getTable('mst_rma_rma_creditmemo'), $condition);
        foreach ((array) $object->getData('credit_memo_ids') as $id) {
            $objArray = [
                'rc_rma_id' => $object->getId(),
                'rc_credit_memo_id' => $id,
            ];
            $this->getConnection()->insert(
                $this->getTable('mst_rma_rma_creditmemo'),
                $objArray
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Rma\Model\Rma $object */
        if (!$object->getIsMassDelete()) {
            $this->loadStoreIds($object);
            $this->loadExchangeOrderIds($object);
            $this->loadReplacementOrderIds($object);
            $this->loadCreditMemoIds($object);
        }

        return parent::_afterLoad($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Rma\Model\Rma $object */
        if (!$object->getId() && !$object->getInTest()) {
            $object->setCreatedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));
            $object->setCode($this->normalize($object->getCode()));
            $object->setStatusHistory($object->getStatusId());
            if ($object->getTicketId()) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                /** @var \Mirasvit\Rma\Api\Config\HelpdeskConfigInterface $helpdeskConfig */
                $helpdeskConfig = $objectManager->create('\Mirasvit\Rma\Api\Config\HelpdeskConfigInterface');
                if ($helpdeskConfig->isHelpdeskActive()) {
                    /** @var \Mirasvit\Helpdesk\Model\Ticket $ticket */
                    $ticket = $objectManager->create('\Mirasvit\Helpdesk\Model\TicketFactory')->create();
                    $resource = $ticket->getResource();
                    $resource->load($ticket, $object->getTicketId());
                    $resource->save($ticket->setStatusId(StatusInterface::CLOSED));
                    ;
                }
            }
        } else {
            $statuses = explode(',', $object->getStatusHistory());
            end($statuses);
            if (current($statuses) != $object->getStatusId()) {
                $statuses[] = $object->getStatusId();
            }
            $object->setStatusHistory(implode(',', $statuses));
        }

        if (!$object->getInTest()) {
            $object->setUpdatedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));
            $object->setLastReplyAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));
        }

        return parent::_beforeSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Rma\Model\Rma $object */
        if (!$object->getIsMassStatus()) {
            $this->saveStoreIds($object);
            $this->saveExchangeOrderIds($object);
            $this->saveReplacementOrderIds($object);
            $this->saveCreditMemoIds($object);
        }

        return parent::_afterSave($object);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function normalize($string)
    {
        $string = $this->productUrl->formatUrlKey($string);
        $string = str_replace('-', '_', $string);

        return 'f_'.$string;
    }
}
