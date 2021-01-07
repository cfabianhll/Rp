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

class Field extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Context
     */
    private $context;
    /**
     * @var \Mirasvit\Rma\Model\FieldFactory
     */
    private $fieldFactory;
    /**
     * @var null
     */
    private $resourcePrefix;

    /**
     * Field constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Mirasvit\Rma\Model\FieldFactory $fieldFactory
     * @param null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Mirasvit\Rma\Model\FieldFactory $fieldFactory,
        $resourcePrefix = null
    ) {
        $this->context        = $context;
        $this->resourcePrefix = $resourcePrefix;
        $this->fieldFactory   = $fieldFactory;

        parent::__construct($context, $resourcePrefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('mst_rma_field', 'field_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Rma\Model\Field $object */
        if (!$object->getIsMassDelete()) {
        }

        return parent::_afterLoad($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Rma\Model\Field $object */
        if (!$object->getId()) {
            $object->setCreatedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));
        }
        $object->setUpdatedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));

        if (is_array($object->getData('visible_customer_status'))) {
            $object->setData(
                'visible_customer_status',
                ','.implode(',', $object->getData('visible_customer_status')).','
            );
        }

        return parent::_beforeSave($object);
    }

    /**
     * @param \Mirasvit\Rma\Model\Field $object
     * @return void
     */
    public function afterCommitCallback(\Mirasvit\Rma\Model\Field $object)
    {
        if ($object->isObjectNew()) {
            $resource = $this->context->getResources();
            $writeConnection = $resource->getConnection('core_write');
            $tableName = $resource->getTableName('mst_rma_rma');
            $fieldType = 'TEXT';
            if ($object->getType() == 'date') {
                $fieldType = 'TIMESTAMP';
            }
            $query = "ALTER TABLE `{$tableName}` ADD `{$object->getCode()}` ".$fieldType;
            $writeConnection->query($query);
            $writeConnection->resetDdlCache();
        }
    }

    /************************/

    /**
     * @var string
     */
    protected $dbCode;

    /**
     * {@inheritdoc}
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        /* @var \Mirasvit\Rma\Model\Field  $object */
        $field = $this->fieldFactory->create()->load($object->getId());
        $this->dbCode = $field->getCode();

        return parent::_beforeDelete($object);
    }

    /**
     * @param \Mirasvit\Rma\Model\Field $object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function afterDeleteCommit(\Mirasvit\Rma\Model\Field  $object)
    {
        $resource = $this->context->getResources();
        $writeConnection = $resource->getConnection('core_write');
        $tableName = $resource->getTableName('mst_rma_rma');
        $query = "ALTER TABLE `{$tableName}` DROP `{$this->dbCode}`";
        $writeConnection->query($query);
        $writeConnection->resetDdlCache();
    }
}
