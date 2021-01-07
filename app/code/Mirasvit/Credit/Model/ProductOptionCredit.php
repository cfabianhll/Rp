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

use Magento\Catalog\Model\Product\Option;

class ProductOptionCredit extends Option implements \Mirasvit\Credit\Api\Data\ProductOptionCreditInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Credit\Model\ResourceModel\ProductOptionCredit');
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionProductId()
    {
        return $this->getData(self::KEY_OPTION_PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionProductId($productId)
    {
        return $this->setData(self::KEY_OPTION_PRODUCT_ID, $productId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(self::KEY_STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::KEY_STORE_ID, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionPriceType()
    {
        return $this->getData(self::KEY_OPTION_PRICE_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionPriceType($optionType)
    {
        return $this->setData(self::KEY_OPTION_PRICE_TYPE, $optionType);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionPriceOptions()
    {
        return $this->getData(self::KEY_OPTION_PRICE_OPTIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionPriceOptions($priceOptions)
    {
        return $this->setData(self::KEY_OPTION_PRICE_OPTIONS, $priceOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionPrice()
    {
        return $this->getData(self::KEY_OPTION_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionPrice($price)
    {
        return $this->setData(self::KEY_OPTION_PRICE, $price);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionCredits()
    {
        return $this->getData(self::KEY_OPTION_CREDITS);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionCredits($credits)
    {
        return $this->setData(self::KEY_OPTION_CREDITS, $credits);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionMinCredits()
    {
        return $this->getData(self::KEY_OPTION_MIN_CREDITS);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionMinCredits($credits)
    {
        return $this->setData(self::KEY_OPTION_MIN_CREDITS, (int)$credits);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionMaxCredits()
    {
        return $this->getData(self::KEY_OPTION_MAX_CREDITS);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionMaxCredits($credits)
    {
        return $this->setData(self::KEY_OPTION_MAX_CREDITS, (int)$credits);
    }

    /**
     * Magento does not call this method, only 3rd party extensions. Override parent method.
     *
     * @param string $type For compatibility with parent only
     * @return \Magento\Catalog\Model\Product\Option\Type\Text
     */
    public function groupFactory($type)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $optionTypeFactory = $objectManager->create('Magento\Catalog\Model\Product\Option\Type\Factory');

        return $optionTypeFactory->create('Magento\Catalog\Model\Product\Option\Type\Text');
    }
}