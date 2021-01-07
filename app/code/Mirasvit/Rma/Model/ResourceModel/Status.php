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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Mirasvit\Rma\Api\Data\RmaInterface;
use Mirasvit\Rma\Api\Data\StatusInterface;
use Mirasvit\Rma\Api\Repository\RmaRepositoryInterface;

class Status extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    private $rmaRepository;

    /**
     * Condition constructor.
     * @RmaRepositoryInterface $rmaRepository
     * @param Context $context
     * @param null $connectionName
     */
    public function __construct(
        RmaRepositoryInterface $rmaRepository,
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);

        $this->rmaRepository = $rmaRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('mst_rma_status', 'status_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($this->isStatusUsed($object)) {
            throw new LocalizedException(
                __('Status "%1" is used in the existing RMA(s). Please remove the status from RMA(s)', $object->getName())
            );
        }
        return parent::_beforeDelete($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Rma\Model\Status $object */
        if (!$object->getId()) {
            $object->setCreatedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));

        }
        $object->setUpdatedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));

        return parent::_beforeSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Rma\Model\Status $object */
        if (!$object->getIsMassStatus()) {
        }

        return parent::_afterSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $status
     *
     * @return bool
     */
    private function isStatusUsed($status)
    {
        $collection = $this->rmaRepository->getCollection()
            ->addFieldToFilter('main_table.'.RmaInterface::KEY_STATUS_ID, $status->getId())
        ;

        return $collection->count() > 0;
    }
}
