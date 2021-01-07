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



namespace Mirasvit\Rma\Helper\Message;

class Url extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @return string
     */
    public function getPostUrl()
    {
        return $this->_urlBuilder->getUrl('rma/rma/savemessage');
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return string
     */
    public function getConfirmationUrl($rma)
    {
        return $this->_urlBuilder->getUrl(
            'rma/rma/savemessage', ['id' => $rma->getGuestId(), 'shipping_confirmation' => true]
        );
    }
}