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

namespace Mageplaza\GiftCard\Helper;

use Magento\Customer\Model\Customer;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\GiftCard\Mail\Template\TransportBuilder;
use Mageplaza\GiftCard\Model\Credit;

/**
 * Class Email
 * @package Mageplaza\GiftCard\Helper
 */
class Email extends Data
{
    const EMAIL_TYPE_DELIVERY      = '';
    const EMAIL_TYPE_UPDATE        = 'update';
    const EMAIL_TYPE_EXPIRE        = 'before_expire';
    const EMAIL_TYPE_NOTICE_SENDER = 'notify_sender';
    const EMAIL_TYPE_UNUSED        = 'after_unused';
    const EMAIL_TYPE_CREDIT        = 'credit';

    /**
     * @var \Mageplaza\GiftCard\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * @var array
     */
    protected $emailParam = [];

    /**
     * Email constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Mageplaza\GiftCard\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation
    )
    {
        parent::__construct($context, $objectManager, $storeManager, $localeDate);

        $this->transportBuilder  = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
    }

    /**
     * Send email to recipient
     *
     * @param $giftCard
     * @param $type
     * @param array $params
     * @return $this
     * @throws \Exception
     * @throws \Spipu\Html2Pdf\Exception\Html2PdfException
     */
    public function sendDeliveryEmail($giftCard, $type, $params = [])
    {
        $validator = ObjectManager::getInstance()->get(\Magento\Framework\Validator\EmailAddress::class);
        if (!$this->isEmailEnable($type, $giftCard->getStoreId()) || !$validator->isValid($giftCard->getDeliveryAddress())) {
            return $this;
        }

        $attachment = ($type == self::EMAIL_TYPE_DELIVERY)
            ? $this->getTemplateHelper()->outputGiftCardPdf($giftCard, 's', TransportBuilder::ATTACHMENT_NAME)
            : null;

        $params = $this->prepareEmailParam($giftCard, $params);

        $this->sendEmailTemplate(
            $type,
            $params['recipient'],
            $giftCard->getDeliveryAddress(),
            $params,
            $giftCard->getStoreId(),
            $attachment
        );

        return $this;
    }

    /**
     * Send email to sender
     *
     * @param $giftCard
     * @param $type
     * @param array $params
     * @return $this
     * @throws \Exception
     */
    public function sendNoticeSenderEmail($giftCard, $type, $params = [])
    {
        if (!$this->isEmailEnable($type, $giftCard->getStoreId())) {
            return $this;
        }

        $order = $this->getGiftCardOrder($giftCard);
        if (!$order || !$order->getId()) {
            return $this;
        }

        $customerEmail = $order->getCustomerEmail();

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore($giftCard->getStoreId());

        /** @var Customer $customer */
        $customer = ObjectManager::getInstance()->create(Customer::class);
        $customer->setStore($store)->loadByEmail($customerEmail);
        if ($customer->getId()) {
            $credit       = $this->objectManager->create(Credit::class)->load($customer->getId(), 'customer_id');
            $notification = is_null($credit->getGiftcardNotification()) ? true : (boolean)$credit->getGiftcardNotification();
            if (!$notification) {
                return $this;
            }
        }

        $params = $this->prepareEmailParam($giftCard, $params);

        $this->sendEmailTemplate(
            $type,
            $params['sender'],
            $customerEmail,
            $params,
            $store->getId()
        );

        return $this;
    }

    /**
     * @param \Mageplaza\GiftCard\Model\GiftCard $giftCard
     * @param                                    $params
     *
     * @return mixed
     */
    protected function prepareEmailParam($giftCard, $params)
    {
        $gcId = $giftCard->getId();
        if (!isset($this->emailParam[$gcId])) {
            $templateFields = $giftCard->getTemplateFields() ? self::jsonDecode($giftCard->getTemplateFields()) : [];

            $this->emailParam[$gcId] = array_merge([
                'sender'          => isset($templateFields['sender']) ? $templateFields['sender'] : '',
                'recipient'       => isset($templateFields['recipient']) ? $templateFields['recipient'] : '',
                'message'         => isset($templateFields['message']) ? $templateFields['message'] : '',
                'balanceFormated' => $this->convertPrice($giftCard->getBalance(), true, false, $giftCard->getStoreId()),
                'status_label'    => $giftCard->getStatusLabel(),
                'expired_date'    => $this->formatDate($giftCard->getExpiredDate()),
                'hidden_code'     => $giftCard->getHiddenCode(),
                'giftcard'        => $giftCard
            ], $params);
        }

        return $this->emailParam[$gcId];
    }

    /**
     * @param       $type
     * @param       $toName
     * @param       $toEmail
     * @param array $templateParams
     * @param null $storeId
     * @param null $attachFile
     *
     * @return $this
     * @throws \Exception
     */
    public function sendEmailTemplate(
        $type,
        $toName,
        $toEmail,
        $templateParams = [],
        $storeId = null,
        $attachFile = null
    )
    {
        $this->inlineTranslation->suspend();

        if (is_null($storeId)) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $template = $this->getEmailConfig($type ? $type . '/template' : 'template', $storeId);
        $sender   = $this->getEmailConfig('sender');

        try {
            $transportBuilder = $this->transportBuilder
                ->setTemplateIdentifier($template)
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars($templateParams)
                ->setFrom($sender)
                ->addTo($toEmail, $toName);

            /* if ($attachFile) {
                $transportBuilder->addAttachment($attachFile);
            } */
            $transportBuilder->getTransport()->sendMessage();

            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            throw $e;
        }

        return $this;
    }
}
