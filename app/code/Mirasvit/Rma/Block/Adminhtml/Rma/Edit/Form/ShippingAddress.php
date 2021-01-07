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



namespace Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form;

class ShippingAddress extends \Magento\Backend\Block\Template
{
    /**
     * @var Generalinfo\CustomFields
     */
    private $customFields;
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    private $formFactory;

    /**
     * ShippingAddress constructor.
     * @param Generalinfo\CustomFields $customFields
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form\Generalinfo\CustomFields $customFields,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->customFields = $customFields;
        $this->formFactory  = $formFactory;

        parent::__construct($context, $data);
    }


    /**
     * General information form
     *
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFormHtml(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        $form = $this->formFactory->create();

        $fieldset = $form->addFieldset('customer_fieldset', []);

        $this->customFields->addCustomerLink($fieldset, $rma);

        $fieldset->addField('firstname', 'text', [
            'label' => __('First Name'),
            'name'  => 'firstname',
            'value' => $rma->getFirstname(),
        ]);
        $fieldset->addField('lastname', 'text', [
            'label' => __('Last Name'),
            'name'  => 'lastname',
            'value' => $rma->getLastname(),
        ]);
        $fieldset->addField('company', 'text', [
            'label' => __('Company'),
            'name'  => 'company',
            'value' => $rma->getCompany(),
        ]);
        $fieldset->addField('telephone', 'text', [
            'label' => __('Telephone'),
            'name'  => 'telephone',
            'value' => $rma->getTelephone(),
        ]);
        $fieldset->addField('email', 'text', [
            'label' => __('Email'),
            'name'  => 'email',
            'value' => $rma->getEmail(),
        ]);
        $street = explode("\n", $rma->getStreet());
        $fieldset->addField('street', 'hidden', [
            'label' => __('Street Address'),
            'name'  => 'street',
            'value' => $street[0],
        ]);
        $fieldset->addField('street2', 'hidden', [
            'name'  => 'street2',
            'value' => isset($street[1]) ? $street[1] : '',
        ]);
        $fieldset->addField('city', 'hidden', [
            'label' => __('City'),
            'name'  => 'city',
            'value' => $rma->getCity(),
        ]);
        $fieldset->addField('postcode', 'hidden', [
            'label' => __('Zip/Postcode'),
            'name'  => 'postcode',
            'value' => $rma->getPostcode(),
        ]);

        return $form->toHtml();
    }
}