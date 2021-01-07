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
 * @package   mirasvit/module-credit
 * @version   1.0.79
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Credit\Helper;

use Magento\Framework\Filter\Translit;
use Mirasvit\Credit\Model\Config;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Pricing\PriceCurrencyInterface as PricingHelper;

class Email extends AbstractHelper
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var Translit
     */
    protected $translit;
    /**
     * @var Calculation
     */
    private $calculationHelper;

    /**
     * @param Calculation      $calculationHelper
     * @param Config           $config
     * @param PricingHelper    $pricingHelper
     * @param Translit         $translit
     * @param TransportBuilder $transportBuilder
     * @param Context          $context
     */
    public function __construct(
        Calculation $calculationHelper,
        Config $config,
        PricingHelper $pricingHelper,
        Translit $translit,
        TransportBuilder $transportBuilder,
        Context $context
    ) {
        $this->calculationHelper = $calculationHelper;
        $this->config            = $config;
        $this->pricingHelper     = $pricingHelper;
        $this->translit          = $translit;
        $this->transportBuilder  = $transportBuilder;

        parent::__construct($context);
    }

    /**
     * @param \Mirasvit\Credit\Model\Transaction $transaction
     * @param bool                               $force
     * @return bool
     */
    public function sendBalanceUpdateEmail($transaction, $force = false)
    {
        $balance = $transaction->getBalance();

        if (!$balance->getIsSubscribed() || !$this->config->isSendBalanceUpdate()) {
            if (!$force) {
                return false;
            }
        }

        $customer       = $balance->getCustomer();
        $recipientEmail = $customer->getEmail();
        $recipientName  = $this->translit->filter($customer->getName());
        $storeId        = $customer->getStore()->getId();

        $amount = $this->calculationHelper->convertToCurrency(
            $balance->getAmount(),
            $balance->getCurrencyCode(),
            $transaction->getCurrencyCode(),
            $customer->getStore()
        );
        $balanceAmount = $this->pricingHelper->format(
            $amount,
            false,
            PricingHelper::DEFAULT_PRECISION,
            null,
            $transaction->getCurrencyCode()
        );
        $transactionAmount = $this->pricingHelper->format(
            $transaction->getBalanceDelta(),
            false,
            PricingHelper::DEFAULT_PRECISION,
            null,
            $transaction->getCurrencyCode()
        );
        $variables = [
            'customer'            => $customer,
            'store'               => $customer->getStore(),
            'transaction'         => $transaction,
            'balance'             => $balance,
            'transaction_amount'  => $transactionAmount,
            'balance_amount'      => $balanceAmount,
            'transaction_message' => $transaction->getEmailMessage(),
        ];

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($this->config->getEmailBalanceUpdateTemplate($storeId))
            ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
            ->setTemplateVars($variables)
            ->setFrom($this->config->getEmailSender($storeId))
            ->addTo($recipientEmail, $recipientName)
            ->getTransport();

        $transport->sendMessage();

        return true;
    }
}
