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



namespace Mirasvit\Rma\Helper\User;

/**
 * Helper which creates different html code
 */
class Html extends \Magento\Framework\App\Helper\AbstractHelper implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    private $userCollectionFactory;
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;

    /**
     * Html constructor.
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->userCollectionFactory = $userCollectionFactory;
        $this->context = $context;

        parent::__construct($context);
    }

    /**
     * @param bool $emptyOption
     * @return array
     */
    public function toAdminUserOptionArray($emptyOption = false)
    {
        $arr = $this->userCollectionFactory->create()->toArray();
        $result = [];
        foreach ($arr['items'] as $value) {
            $result[] = ['value' => $value['user_id'], 'label' => $value['firstname'] . ' ' . $value['lastname']];
        }
        if ($emptyOption) {
            array_unshift($result, ['value' => 0, 'label' => __('-- Please Select --')]);
        }

        return $result;
    }

    /**
     * @param bool $emptyOption
     * @return array
     */
    public function getAdminUserOptionArray($emptyOption = false)
    {
        $arr = $this->userCollectionFactory->create()->toArray();
        $result = [];
        foreach ($arr['items'] as $value) {
            $result[$value['user_id']] = $value['firstname'] . ' ' . $value['lastname'];
        }
        if ($emptyOption) {
            $result[0] = __('-- Please Select --');
        }

        return $result;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->toAdminUserOptionArray(false);
    }

}