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



namespace Mirasvit\Credit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\ProductFactory;

class Config
{
    const REFILL_PRODUCT_SKU = 'credit';

    const USE_CREDIT_UNDEFINED = 0;
    const USE_CREDIT_NO = 1;
    const USE_CREDIT_YES = 2;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\Product|bool
     */
    protected $refillProduct;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductFactory $productFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductFactory $productFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->productFactory = $productFactory;
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getShareBalance($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'credit/general/share_balance',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isAutoRefundEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'credit/general/auto_refund_enabled',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isAutoApplyEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'credit/general/auto_apply_enabled',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getEmailBalanceUpdateTemplate($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'credit/email/balance_update_template',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isSendBalanceUpdate($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'credit/email/send_balance_update',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getEmailSender($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'credit/email/email_identity',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return \Magento\Catalog\Model\Product|false
     */
    public function getRefillProduct()
    {
        if ($this->refillProduct === null) {
            $this->refillProduct = false;

            $product = $this->productFactory->create();
            if ($id = $product->getIdBySku(self::REFILL_PRODUCT_SKU)) {
                $product = $product->load($id);
                if ($product->isAvailable()) {
                    $this->refillProduct = $product;
                }
            }
        }

        return $this->refillProduct;
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getAdvancedOrderExpr($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'credit/advanced/order_expr',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getAdvancedCreditMemoExpr($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'credit/advanced/creditmemo_expr',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
