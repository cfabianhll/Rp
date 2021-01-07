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

/**
 * Helper which creates different html code
 */
class Html extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return string
     */
    public function getTextHtml(\Mirasvit\Rma\Api\Data\MessageInterface $message)
    {
        if ($message->getIsHtml()) {
            return $message->getText();
        } else {
            return $this->convertToHtml($message->getText());
        }
    }

    /**
     * @param string $text
     * @return string
     */
    public function convertToHtml($text)
    {
        return nl2br($text);
    }

}