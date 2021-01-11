<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\PurchaseOrderSuccess\Service\Config;

use DateTime;
use Exception;
use Magestore\PurchaseOrderSuccess\Api\Data\PurchaseOrderInterface;
use Magestore\PurchaseOrderSuccess\Block\Adminhtml\Form\Field\Status;
use Magestore\PurchaseOrderSuccess\Model\PurchaseOrder\Option\PaymentTerm as PaymentTermOption;

/**
 * Class PaymentTerm
 *
 * Used for payment term
 */
class PaymentTerm extends AbstractConfig
{
    const PURCHASE_ORDER_CONFIG_PATH = 'purchaseordersuccess/payment_term/payment_term';

    /**
     * Init new config
     *
     * @param PurchaseOrderInterface $purchaseOrder
     * @return string
     */
    public function initNewConfig(PurchaseOrderInterface $purchaseOrder)
    {
        if (!$purchaseOrder->getPaymentTerm()) {
            $purchaseOrder->setPaymentTerm(PaymentTermOption::OPTION_NONE_VALUE);
        }
        if ($purchaseOrder->getPaymentTerm() == PaymentTermOption::OPTION_NEW_VALUE) {
            $purchaseOrder->setPaymentTerm($purchaseOrder->getData('new_payment_term'));
        }
        return $purchaseOrder->getPaymentTerm();
    }

    /**
     * Is none value method
     *
     * @param PurchaseOrderInterface $purchaseOrder
     * @return bool
     */
    public function isNoneValueMethod($purchaseOrder)
    {
        if ($purchaseOrder->getPaymentTerm() == PaymentTermOption::OPTION_NONE_VALUE) {
            return true;
        }
        return false;
    }

    /**
     * Generate new element config.
     *
     * @param string $newConfig
     * @return array
     */
    public function generateNewConfig($newConfig)
    {
        return [
            'name' => $newConfig,
            'description' => '',
            'status' => Status::ENABLE_VALUE
        ];
    }

    /**
     * Save New Config
     *
     * @param array $configValue
     * @param string $newConfig
     * @throws Exception
     */
    public function saveNewConfig($configValue, $newConfig)
    {
        $date = new DateTime();
        $configValue[$date->getTimestamp() + 100] = $this->generateNewConfig($newConfig);
        $config = $this->configFactory->create();
        $config->setDataByPath(
            static::PURCHASE_ORDER_CONFIG_PATH,
            $configValue
        );
        try {
            $config->save();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
