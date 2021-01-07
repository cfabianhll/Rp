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



namespace Mirasvit\Rma\Api\Service\Field;

interface FieldManagementInterface
{

    /**
     * @return \Mirasvit\Rma\Model\Field[]
     */
    public function getEditableCustomerCollection();

    /**
     * @param string $status
     * @param bool   $isEdit
     *
     * @return \Mirasvit\Rma\Model\Field[]
     */
    public function getVisibleCustomerCollection($status, $isEdit);

    /**
     * @return \Mirasvit\Rma\Model\Field[]
     */
    public function getShippingConfirmationFields();

    /**
     * @return \Mirasvit\Rma\Model\Field[]
     */
    public function getStaffCollection();

    /**
     * @return \Mirasvit\Rma\Model\Field[]
     */
    public function getGridStaffCollection();

    /**
     * @param \Mirasvit\Rma\Model\Field          $field
     * @param bool                               $staff
     * @param bool|\Magento\Framework\DataObject $object
     *
     * @return array
     */
    public function getInputParams($field, $staff = true, $object = false);

    /**
     * @param \Mirasvit\Rma\Model\Field $field
     *
     * @return string
     */
    public function getInputHtml($field);

    /**
     * @param array                         $post
     * @param \Magento\Framework\DataObject $object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function processPost($post, $object);

    /**
     * @param \Magento\Framework\DataObject $object
     * @param \Mirasvit\Rma\Model\Field     $field
     *
     * @return bool|string
     */
    public function getValue($object, $field);

    /**
     * @param string $code
     *
     * @return null|\Mirasvit\Rma\Model\Field
     */
    public function getFieldByCode($code);
}