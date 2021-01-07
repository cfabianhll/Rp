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
 * @package   mirasvit/module-optimize
 * @version   1.0.5
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Optimize\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class YesNoField extends Field
{
    protected function _renderValue(AbstractElement $element)
    {
        $data = $element->getData('original_data');

        /** @var Template $block */
        $block = $this->getLayout()->createBlock(Template::class);
        $block->setTemplate('Mirasvit_Optimize::system/config/comment.phtml')
            ->setData($data)
            ->setData('elementId', $element->getId());

        $element->setData('comment', $block->toHtml());

        return parent::_renderValue($element);
    }
}
