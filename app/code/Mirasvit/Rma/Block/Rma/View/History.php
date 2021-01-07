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



namespace Mirasvit\Rma\Block\Rma\View;

class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mirasvit\Rma\Api\Service\Message\MessageManagementInterface
     */
    private $messageManagement;
    /**
     * @var \Mirasvit\Rma\Helper\Attachment\Url
     */
    private $rmaAttachmentUrl;
    /**
     * @var \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface
     */
    private $attachmentManagement;
    /**
     * @var \Mirasvit\Rma\Helper\Message\Html
     */
    private $rmaMessageHtml;
    /**
     * @var \Mirasvit\Rma\Api\Service\Message\MessageManagement\SearchInterface
     */
    private $messageSearchManagement;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * History constructor.
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Registry $registry
     * @param \Mirasvit\Rma\Api\Service\Message\MessageManagementInterface $messageManagement
     * @param \Mirasvit\Rma\Api\Service\Message\MessageManagement\SearchInterface $messageSearchManagement
     * @param \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface $attachmentManagement
     * @param \Mirasvit\Rma\Helper\Message\Html $rmaMessageHtml
     * @param \Mirasvit\Rma\Helper\Attachment\Url $rmaAttachmentUrl
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Registry $registry,
        \Mirasvit\Rma\Api\Service\Message\MessageManagementInterface $messageManagement,
        \Mirasvit\Rma\Api\Service\Message\MessageManagement\SearchInterface $messageSearchManagement,
        \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface $attachmentManagement,
        \Mirasvit\Rma\Helper\Message\Html $rmaMessageHtml,
        \Mirasvit\Rma\Helper\Attachment\Url $rmaAttachmentUrl,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->searchCriteriaBuilder   = $searchCriteriaBuilder;
        $this->registry                = $registry;
        $this->messageManagement       = $messageManagement;
        $this->messageSearchManagement = $messageSearchManagement;
        $this->attachmentManagement    = $attachmentManagement;
        $this->rmaMessageHtml          = $rmaMessageHtml;
        $this->rmaAttachmentUrl        = $rmaAttachmentUrl;
        $this->context                 = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     */
    public function getRma()
    {
        return $this->registry->registry('current_rma');
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\MessageInterface[]
     */
    public function getMessages()
    {
        $rma = $this->getRma();

        return $this->messageSearchManagement->getVisibleInFront($rma);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return string
     */
    public function getTextHtml(\Mirasvit\Rma\Api\Data\MessageInterface $message)
    {
        return $this->rmaMessageHtml->getTextHtml($message);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return \Mirasvit\Rma\Api\Data\AttachmentInterface[]
     */
    public function getAttachments(\Mirasvit\Rma\Api\Data\MessageInterface $message)
    {
        return $this->attachmentManagement->getAttachmentsByMessage($message);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\AttachmentInterface $attachment
     * @return string
     */
    public function getAttachmentUrl(\Mirasvit\Rma\Api\Data\AttachmentInterface $attachment)
    {
        return $this->rmaAttachmentUrl->getUrl($attachment);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return \Magento\User\Model\User
     */
    public function getUser(\Mirasvit\Rma\Api\Data\MessageInterface $message)
    {
        return $this->messageManagement->getUser($message);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return string
     */
    public function getBodyStyles(\Mirasvit\Rma\Api\Data\MessageInterface $message)
    {
        $bodyStyles = '';
        if ($message->getCustomerName()) {
            $bodyStyles .= '__customer';
        } else {
            if (!$message->getStatusId()) {
                $bodyStyles .= '__user';
            } else {
                $bodyStyles .= '__system';
            }
        }

        return $bodyStyles;
    }
}
