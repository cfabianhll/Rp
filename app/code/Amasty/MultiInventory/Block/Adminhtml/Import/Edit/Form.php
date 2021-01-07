<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Import\Edit;

use Amasty\MultiInventory\Model\Import\Source\FileTypeFactory;
use Amasty\MultiInventory\Model\Import\Source\IdentifierFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

/**
 * Class Form
 */
class Form extends Generic
{
    /**
     * @var IdentifierFactory
     */
    private $identifier;

    /**
     * @var FileTypeFactory
     */
    private $fileType;

    /**
     * @var Yesno
     */
    private $yesNo;

    /**
     * Form constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param IdentifierFactory $identifier
     * @param FileTypeFactory $fileType
     * @param Yesno $yesNo
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        IdentifierFactory $identifier,
        FileTypeFactory $fileType,
        Yesno $yesNo,
        array $data = []
    ) {
        $this->identifier = $identifier;
        $this->fileType = $fileType;
        $this->yesNo = $yesNo;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        $fieldsets['base'] = $form->addFieldset('base_fieldset', ['legend' => __('Import Settings')]);

        $fieldsets['base']->addField(
            'identifier',
            'select',
            [
                'name' => 'identifier',
                'title' => __('Product Identifier'),
                'label' => __('Product Identifier'),
                'required' => true,
                'values' => $this->identifier->create()->toOptionArray(),
                'note' => 'Choose SKU or ID to identify the product'
            ]
        );

        $fieldsets['base']->addField(
            'file_type',
            'select',
            [
                'name' => 'file_type',
                'title' => __('File Type'),
                'label' => __('File Type'),
                'required' => true,
                'onchange' => 'varienImport.disableSeparated(\'file_type\', \'import_field_separator\');
                 varienImport.acceptFormat(\'file_type\', \'import_file\');',
                'values' => $this->fileType->create()->toOptionArray()
            ]
        );

        $fieldsets['base']->addField(
            'import_field_separator',
            'text',
            [
                'name' => 'import_field_separator',
                'label' => __('Field separator'),
                'title' => __('Field separator'),
                'required' => true,
                'value' => ',',
            ]
        );
        $fieldsets['base']->addField(
            'export_save',
            'select',
            [
                'name' => 'export_save',
                'label' => __('Export & Save the current stock file before import?'),
                'title' => __('Export & Save the current stock file before import?'),
                'values' => $this->yesNo->toOptionArray(),
                'value' => 1,
                'note' => 'It can be used in order to revert back the changes.'
            ]
        );

        $fieldsets['upload'] = $form->addFieldset(
            'upload_file_fieldset',
            ['legend' => __('File to Import')]
        );

        // add field with use file-uploader
        $fieldsets['upload']->addField(
            'import_file',
            'file',
            [
                'name' => 'import_file',
                'label' => __('Select File to Import'),
                'title' => __('Select File to Import'),
                'required' => true,
                'after_element_html' => /** @lang text */ '
<span class="action-default" id="browse_button">Browse File</span>
<div id="out_file"></div>
<script type="text/x-magento-template" id="file-template">
        <div id="<%- data.id %>" class="file-row">
            <span class="file-info"><%- data.name %> (<%- data.size %>)</span>
                <div data-action="show-success"></div>
                <div data-action="show-notice"></div>
                <div data-action="show-error"></div>
            <div class="clear"></div>
        </div>
    </script>'
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
