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


namespace Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form\Element;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory as ElementCollectionFactory;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Framework\Escaper;
use Magento\Framework\View\Asset\Repository;
use Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface;

/**
 * @method \Mirasvit\Rma\Model\Attachment getAttachment()
 * @method $this setAttachment($param)
 */
class File extends AbstractElement
{
    /**
     * @var AttachmentManagementInterface
     */
    private $attachmentManagement;
    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * File constructor.
     * @param AttachmentManagementInterface $attachmentManagement
     * @param Repository $assetRepo
     * @param ElementFactory $factoryElement
     * @param ElementCollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        AttachmentManagementInterface $attachmentManagement,
        Repository $assetRepo,
        ElementFactory $factoryElement,
        ElementCollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        $this->assetRepo = $assetRepo;
        $this->attachmentManagement = $attachmentManagement;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);

        $this->setType('file');
    }

    /**
     * {@inheritdoc}
     */
    public function getElementHtml()
    {
        $this->addClass('input-file');
        if ($this->getRequired()) {
            $this->removeClass('required-entry');
            $this->addClass('required-file');
        }

        $element = sprintf(
            '<input id="%s" name="%s" %s />%s%s',
            $this->getHtmlId(),
            $this->getName(),
            $this->serialize($this->getHtmlAttributes()),
            $this->getAfterElementHtml(),
            $this->_getHiddenInput()
        );

        return $this->getPreviewHtml() . $this->getDeleteCheckboxHtml() . $element;
    }

    /**
     * Return Delete File CheckBox HTML
     *
     * @return string
     */
    protected function getDeleteCheckboxHtml()
    {
        $html = '';
        if ($this->getValue() && !$this->getRequired() && !is_array($this->getValue())) {
            $checkboxId = sprintf('%s_delete', $this->getHtmlId());
            $checkbox = [
                'type'  => 'checkbox',
                'name'  => sprintf('%s[delete]', $this->getName()),
                'value' => '1',
                'class' => 'checkbox',
                'id'    => $checkboxId
            ];
            $label = ['for' => $checkboxId];
            if ($this->getDisabled()) {
                $checkbox['disabled'] = 'disabled';
                $label['class'] = 'disabled';
            }

            $html .= '<span class="' . $this->_getDeleteCheckboxSpanClass() . '">';
            $html .= $this->_drawElementHtml('input', $checkbox) . ' ';
            $html .= $this->_drawElementHtml('label', $label, false) . $this->_getDeleteCheckboxLabel() . '</label>';
            $html .= '</span>';
        }
        return $html;
    }

    /**
     * Return Delete CheckBox SPAN Class name
     *
     * @return string
     */
    protected function _getDeleteCheckboxSpanClass()
    {
        return 'delete-file';
    }

    /**
     * Return Delete CheckBox Label
     *
     * @return \Magento\Framework\Phrase
     */
    protected function _getDeleteCheckboxLabel()
    {
        return __('Delete File');
    }

    /**
     * Return File preview link HTML
     *
     * @return string
     */
    protected function getPreviewHtml()
    {
        $html = '';
        if ($this->getValue() && !is_array($this->getValue())) {
            $url = $this->_getPreviewUrl();
            $html .= '<span class="download-file">';
            $html .= '<a href="' . $url . '">' . $this->getValue() . '</a>';
            $html .= '</span>';
        }
        return $html;
    }

    /**
     * Return Hidden element with current value
     *
     * @return string
     */
    protected function _getHiddenInput()
    {
        return $this->_drawElementHtml(
            'input',
            [
                'type'  => 'hidden',
                'name'  => sprintf('%s[value]', $this->getName()),
                'id'    => sprintf('%s_value', $this->getHtmlId()),
                'value' => $this->getEscapedValue()
            ]
        );
    }

    /**
     * Return Preview/Download URL
     *
     * @return string
     */
    protected function _getPreviewUrl()
    {
        return $this->attachmentManagement->getUrl($this->getAttachment());
    }

    /**
     * Return Element HTML
     *
     * @param string $element
     * @param array  $attributes
     * @param bool   $closed
     * @return string
     */
    protected function _drawElementHtml($element, array $attributes, $closed = true)
    {
        $parts = [];
        foreach ($attributes as $k => $v) {
            $parts[] = sprintf('%s="%s"', $k, $v);
        }

        return sprintf('<%s %s%s>', $element, implode(' ', $parts), $closed ? ' /' : '');
    }

    /**
     * {@inheritdoc}
     */
    public function getEscapedValue($index = null)
    {
        if (is_array($this->getValue())) {
            return false;
        }
        $value = $this->getValue();
        if (is_array($value) && $index === null) {
            $index = 'value';
        }

        return parent::getEscapedValue($index);
    }

    /**
     * Attached file name
     *
     * @return string
     */
    public function getValue()
    {
        if ($this->getAttachment()) {
            return $this->getAttachment()->getName();
        }
    }
}
