<?php
/**
 * Class Notifiy Doc Comment
 *
 * PHP version 7
 *
 * @category Sparsh Technologies
 * @package  Sparsh_MaintenanceMode
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\MaintenanceMode\Cron;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Area;

/**
 * Class Notifiy Doc Comment
 *
 * @category Sparsh Technologies
 * @package  Sparsh_MaintenanceMode
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Notifiy
{
    /**
     * NotifyUserFactory
     *
     * @var \Sparsh\MaintenanceMode\Model\NotifyUserFactory
     */
    protected $notifyUserFactory;

    /**
     * Maintenance Helper
     *
     * @var \Sparsh\MaintenanceMode\Helper\Data
     */
    protected $helperData;

    /**
     * StoreManagerInterface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ScopeConfigInterface
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * InlineTranslation
     *
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * TransportBuilder
     *
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * Notifiy constructor.
     * @param \Sparsh\MaintenanceMode\Model\NotifyUserFactory $notifyUserFactory
     * @param \Sparsh\MaintenanceMode\Helper\Data $helperData
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Sparsh\MaintenanceMode\Model\NotifyUserFactory $notifyUserFactory,
        \Sparsh\MaintenanceMode\Helper\Data $helperData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->notifyUserFactory = $notifyUserFactory;
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->escaper = $escaper;
    }

    /**
     * Notify Cron Ececute Method
     *
     * @return this
     */
    public function execute()
    {
        if ($this->helperData->getIsEnable() == 0) {
            $this->inlineTranslation->suspend();
            try {
                $emails = [];
                $notifyUserModel = $this->notifyUserFactory->create();
                $notifyUsersCollection = $notifyUserModel->getCollection()->addFieldToFilter('is_notified', '0');

                foreach ($notifyUsersCollection as $user) {
                    array_push($emails, $user['email']);
                }

                $storeEmailAddress = $this->scopeConfig->getValue(
                    'trans_email/ident_general/email',
                    ScopeInterface::SCOPE_STORE
                );

                $storeName = $this->scopeConfig->getValue(
                    'trans_email/ident_general/name',
                    ScopeInterface::SCOPE_STORE
                );

                $templateId = $this->scopeConfig->getValue(
                    'maintenancemode/general/newsletter_email_template',
                    ScopeInterface::SCOPE_STORE
                );

                if (!$storeName) {
                    $storeName = $storeEmailAddress;
                }

                if (!empty($emails) && count($emails) > 0 && $storeEmailAddress && $templateId) {

                    $from = [
                        'name' => $this->escaper->escapeHtml($storeName),
                        'email' => $this->escaper->escapeHtml($storeEmailAddress)
                    ];

                    foreach ($emails as $to) {
                        $transport = $this->transportBuilder
                            ->setTemplateIdentifier($templateId)
                            ->setTemplateOptions(
                                [
                                    'area' => Area::AREA_FRONTEND,
                                    'store' => $this->storeManager->getStore()->getId(),
                                ]
                            )
                            ->setTemplateVars(
                                ['email' => $storeEmailAddress]
                            )
                            ->setFrom($from)
                            ->addTo($to, 'name')
                            ->getTransport();

                        $transport->sendMessage();

                        $notifyUserCollection = $notifyUserModel->getCollection()->addFieldToFilter('email', $to)
                            ->addFieldToFilter('is_notified', '0');
                        foreach ($notifyUserCollection as $user) {
                            $user->setIsNotified('1');
                            $user->setUpdatedAt($this->helperData->getStoreDateTime());
                            $user->save();
                        }
                    }
                }

            } finally {
                $this->inlineTranslation->resume();
            }
        }

        return $this;
    }
}
