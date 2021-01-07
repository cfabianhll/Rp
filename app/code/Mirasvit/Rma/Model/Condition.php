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



namespace Mirasvit\Rma\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Mirasvit\Rma\Api\Data\ItemInterface;
use Mirasvit\Rma\Repository\ItemRepository;
use Mirasvit\Rma\Repository\OfflineItemRepository;

/**
 * @method \Mirasvit\Rma\Model\ResourceModel\Condition\Collection|\Mirasvit\Rma\Model\Condition[] getCollection()
 * @method \Mirasvit\Rma\Model\Condition load(int $id)
 * @method bool getIsMassDelete()
 * @method \Mirasvit\Rma\Model\Condition setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method \Mirasvit\Rma\Model\Condition setIsMassStatus(bool $flag)
 * @method \Mirasvit\Rma\Model\ResourceModel\Condition getResource()
 */
class Condition extends \Magento\Framework\Model\AbstractModel implements IdentityInterface,
    \Mirasvit\Rma\Api\Data\ConditionInterface
{
    const CACHE_TAG = 'rma_condition';

    /**
     * {@inheritdoc}
     */
    protected $_cacheTag = 'rma_condition';

    /**
     * {@inheritdoc}
     */
    protected $_eventPrefix = 'rma_condition';
    /**
     * @var \Mirasvit\Rma\Helper\Locale
     */
    private $localeData;

    private $itemRepository;

    private $offlineItemRepository;

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rma\Model\ResourceModel\Condition');
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @param \Mirasvit\Rma\Helper\Locale $localeData
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        ItemRepository $itemRepository,
        OfflineItemRepository $offlineItemRepository,
        \Mirasvit\Rma\Helper\Locale $localeData,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->localeData            = $localeData;
        $this->itemRepository        = $itemRepository;
        $this->offlineItemRepository = $offlineItemRepository;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->localeData->getLocaleValue($this, self::KEY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->localeData->setLocaleValue($this, self::KEY_NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::KEY_SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::KEY_SORT_ORDER, $sortOrder);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->getData(self::KEY_IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::KEY_IS_ACTIVE, $isActive);
    }

    /**
     * @inheritDoc
     */
    function beforeDelete()
    {
        if ($this->isConditionUsed()) {
            throw new LocalizedException(
                __('Condition "%1" is used in the existing RMA(s). Please remove the condition from RMA(s)', $this->getName())
            );
        }
        return parent::beforeDelete();
    }

    /**
     * @return bool
     */
    private function isConditionUsed()
    {
        $collection = $this->itemRepository->getCollection()
            ->addFieldToFilter('main_table.'.ItemInterface::KEY_CONDITION_ID, $this->getId())
        ;
        $offlineCollection = $this->offlineItemRepository->getCollection()
            ->addFieldToFilter('main_table.'.ItemInterface::KEY_CONDITION_ID, $this->getId())
        ;

        return $collection->count() > 0 || $offlineCollection->count() > 0;
    }
}
