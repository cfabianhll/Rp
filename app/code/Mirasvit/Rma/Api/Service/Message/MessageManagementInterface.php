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



namespace Mirasvit\Rma\Api\Service\Message;


interface MessageManagementInterface
{
    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return \Magento\User\Model\User
     */
    public function getUser(\Mirasvit\Rma\Api\Data\MessageInterface $message);

    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return int
     */
    public function getTriggeredBy(\Mirasvit\Rma\Api\Data\MessageInterface $message);

    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return string
     */
    public function getCustomerEmail(\Mirasvit\Rma\Api\Data\MessageInterface $message);

    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return string
     */
    public function getUserName(\Mirasvit\Rma\Api\Data\MessageInterface $message);

    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return string
     */
    public function getType(\Mirasvit\Rma\Api\Data\MessageInterface $message);

    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return string
     */
    public function getFrontendType(\Mirasvit\Rma\Api\Data\MessageInterface $message);

    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return string
     */
    public function getAuthorName(\Mirasvit\Rma\Api\Data\MessageInterface $message);

    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return string
     */
    public function getTextHtml(\Mirasvit\Rma\Api\Data\MessageInterface $message);

    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return \Mirasvit\Rma\Api\Data\AttachmentInterface[]
     */
    public function getAttachments(\Mirasvit\Rma\Api\Data\MessageInterface $message);
}
