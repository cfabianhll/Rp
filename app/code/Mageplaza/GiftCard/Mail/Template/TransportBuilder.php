<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_GiftCard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GiftCard\Mail\Template;

use Magento\Framework\Mail\Template\TransportBuilder as DefaultBuilder;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
/**
 * Class TransportBuilder
 * @package Mageplaza\GiftCard\Mail\Template
 */
class TransportBuilder extends DefaultBuilder
{
    /**
     * Attachment name
     */
    const ATTACHMENT_NAME = 'gift_card.pdf';
     protected $message;
    
    public function __construct(
        \Magento\Framework\Mail\Template\FactoryInterface $templateFactory,
        MessageInterface $message,
        \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        MessageInterfaceFactory $messageFactory = null,
        \Mageplaza\GiftCard\Mail\EmailMessage $giftmessage
    ) {
        $this->templateFactory = $templateFactory;
        $this->objectManager = $objectManager;
        $this->_senderResolver = $senderResolver;
        $this->mailTransportFactory = $mailTransportFactory;
        $this->messageFactory = $messageFactory ?: $this->objectManager->get(MessageInterfaceFactory::class);
        $this->message = $this->messageFactory->create();
        $this->giftmessage = $giftmessage;
        parent::__construct($templateFactory,$message,$senderResolver,$objectManager,$mailTransportFactory,$messageFactory);
    }
     
    /**
     * @param $attachFile
     * @param string $mimeType
     * @param string $disposition
     * @param string $encoding
     * @param string $filename
     * @return $this
     */
    /* public function addAttachment(
        $attachFile,
        $mimeType = 'application/pdf',
        $disposition = \Zend_Mime::DISPOSITION_ATTACHMENT,
        $encoding = \Zend_Mime::ENCODING_BASE64,
        $filename = self::ATTACHMENT_NAME
    )
    {
        $this->message->createAttachment($attachFile, $mimeType, $disposition, $encoding, $filename);

        return $this;
    } */
    
    public function addAttachment($attachFile,
        $mimeType = 'application/pdf',
        $disposition = \Zend_Mime::DISPOSITION_ATTACHMENT,
        $encoding = \Zend_Mime::ENCODING_BASE64,
        $filename = self::ATTACHMENT_NAME)
    {
        $this->message->setBodyAttachment($attachFile, $mimeType, $disposition, $encoding, $filename);
    
        return $this;
    }
    
    /**
     * After all parts are set, add them to message body.
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareMessage()
    {
        parent::prepareMessage();

        $this->giftmessage->setPartsToBody();

        return $this;
    }
}
