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


namespace Mirasvit\Rma\Service\Message;

use \Mirasvit\Rma\Api\Data\MessageInterface;

/**
 *  We put here only methods directly connected with Message properties
 */
class MessageManagement implements \Mirasvit\Rma\Api\Service\Message\MessageManagementInterface
{
    /**
     * @var \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface
     */
    private $attachmentManagement;
    /**
     * @var \Mirasvit\Rma\Helper\Message\Html
     */
    private $messageHtmlHelper;
    /**
     * @var \Magento\User\Model\UserFactory
     */
    private $userFactory;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * MessageManagement constructor.
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Mirasvit\Rma\Helper\Message\Html $messageHtmlHelper
     * @param \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface $attachmentManagement
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \Mirasvit\Rma\Helper\Message\Html $messageHtmlHelper,
        \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface $attachmentManagement
    ) {
        $this->customerFactory       = $customerFactory;
        $this->userFactory           = $userFactory;
        $this->messageHtmlHelper     = $messageHtmlHelper;
        $this->attachmentManagement  = $attachmentManagement;
    }


    /**
     * {@inheritdoc}
     */
    public function getUser(MessageInterface $message)
    {
        return $this->userFactory->create()->load($message->getUserId());
    }

    /**
     * {@inheritdoc}
     */
    public function getTriggeredBy(MessageInterface $message)
    {
        if ($message->getUserId()) {
            return \Mirasvit\Rma\Api\Config\RmaConfigInterface::USER;
        } elseif ($message->getCustomerId()) {
            return \Mirasvit\Rma\Api\Config\RmaConfigInterface::CUSTOMER;
        } elseif ($message->getCustomerName()) {
            return \Mirasvit\Rma\Api\Config\RmaConfigInterface::CUSTOMER; //guest
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerEmail(MessageInterface $message)
    {
        if ($message->getCustomerId()) {
            return $this->customerFactory->create()->load($message->getCustomerId())->getEmail();
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getUserName(MessageInterface $message)
    {
        if ($message->getUserId()) {
            return $this->userFactory->create()->load($message->getUserId())->getName();
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getType(MessageInterface $message)
    {
        if ($message->getIsVisibleInFrontend()) {
            return MessageInterface::COMMENT_PUBLIC;
        }

        return MessageInterface::COMMENT_INTERNAL;
    }

    /**
     * {@inheritdoc}
     */
    public function getFrontendType(MessageInterface $message)
    {
        $bodyStyles = '__system';
        if ($message->getCustomerName()) {
            $bodyStyles = '__customer';
        } elseif (!$message->getStatusId()) {
            $bodyStyles = '__user';
        }

        return $bodyStyles;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorName(MessageInterface $message)
    {
        $name = '';
        if ($message->getCustomerName()) {
            $name = $message->getCustomerName();
        } elseif (($user = $this->getUser($message)) && trim($user->getName()) && !$message->getStatusId()) {
            $name = $user->getName();
        }

        return $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getTextHtml(MessageInterface $message)
    {
        if ($message->getIsHtml()) {
            return $message->getText();
        } else {
            return $this->messageHtmlHelper->convertToHtml($message->getText());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachments(MessageInterface $message)
    {
        return $this->attachmentManagement->getAttachments(
            \Mirasvit\Rma\Api\Config\AttachmentConfigInterface::ATTACHMENT_ITEM_MESSAGE, $message->getId()
        );
    }
}
