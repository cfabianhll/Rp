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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuickResponse extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('mst_rma_template', 'template_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return \Magento\Framework\Model\AbstractModel|\Mirasvit\Rma\Model\QuickResponse
     */
    protected function loadStoreIds(\Magento\Framework\Model\AbstractModel $object)
    {
        /* @var  \Mirasvit\Rma\Model\QuickResponse $object */
        $select = $this->getConnection()->select()
            ->from($this->getTable('mst_rma_template_store'))
            ->where('ts_template_id = ?', $object->getId());
        if ($data = $this->getConnection()->fetchAll($select)) {
            $array = [];
            foreach ($data as $row) {
                $array[] = $row['ts_store_id'];
            }
            $object->setData('store_ids', $array);
        }

        return $object;
    }

    /**
     * @param \Mirasvit\Rma\Model\QuickResponse $object
     * @return void
     */
    protected function saveStoreIds($object)
    {
        /* @var  \Mirasvit\Rma\Model\QuickResponse $object */
        $condition = $this->getConnection()->quoteInto('ts_template_id = ?', $object->getId());
        $this->getConnection()->delete($this->getTable('mst_rma_template_store'), $condition);
        foreach ((array) $object->getData('store_ids') as $id) {
            $objArray = [
                'ts_template_id' => $object->getId(),
                'ts_store_id' => $id,
            ];
            $this->getConnection()->insert(
                $this->getTable('mst_rma_template_store'),
                $objArray
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Rma\Model\QuickResponse $object */
        if (!$object->getIsMassDelete()) {
            $this->loadStoreIds($object);
        }

        return parent::_afterLoad($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Rma\Model\QuickResponse $object */
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
        /** @var  \Mirasvit\Rma\Model\QuickResponse $object */
        if (!$object->getIsMassStatus()) {
            $this->saveStoreIds($object);
        }

        return parent::_afterSave($object);
    }

    /************************/
}
