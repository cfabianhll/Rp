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



namespace Mirasvit\Rma\Model\UI;

class GenericDeleteButton extends GenericButton
{

    /**
     * @return array
     */
    public function getButtonData()
    {
        $url = $this->getUrl('*/*/delete', [self::ID_NAME => $this->getId()]);

        return [
            'label'      => __('Delete'),
            'on_click'   => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $url . '\')',
            'class'      => 'delete',
            'sort_order' => 20,
        ];
    }

}