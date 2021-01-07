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



namespace Mirasvit\Rma\Controller\Adminhtml\Field;

use Mirasvit\Rma\Api\Data\FieldInterface;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Mirasvit\Rma\Controller\Adminhtml\Field
{

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($data = $this->getRequest()->getParams()) {
            if (empty($data['id']) && empty($data[FieldInterface::ID])) { // check if field already exists
                $code = $this->escaper->escapeHtml($data[FieldInterface::KEY_CODE]);
                $field = $this->fieldManagement->getFieldByCode($code);
                if ($field && $field->getId()) {
                    $this->messageManager->addErrorMessage(__('Field with the same code already exists'));
                    $this->backendSession->setFormData($data);
                    $this->_redirect('*/*/add');
                    return;
                }
            }
            $data[FieldInterface::KEY_CODE] = $this->fieldManagement->filterCode($data[FieldInterface::KEY_CODE]);
            $field = $this->_initField();
            $field->setName($data['name']);
            unset($data['name']);
            $field->setValues($data['values']);
            unset($data['values']);
            $field->addData($data);
            //format date to standart
            // $format = $this->localeDate->getDateFormat(\IntlDateFormatter::SHORT);
            // $this->mstcoreDate->formatDateForSave($field, 'active_from', $format);
            // $this->mstcoreDate->formatDateForSave($field, 'active_to', $format);
            if (!isset($data['visible_customer_status'])) {
                $field->setVisibleCustomerStatus([]);
            }
            try {
                $field->save();

                $this->messageManager->addSuccessMessage(__('Field was successfully saved'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['id' => $field->getId(), 'store' => $field->getStoreId()]
                    );
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->backendSession->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find Field to save'));

        return $resultRedirect->setPath('*/*/');
    }
}
