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

namespace Mageplaza\GiftCard\Api;

/**
 * Interface GiftCardManagementInterface
 *
 * @package Mageplaza\GiftCard\Api
 */
interface GiftCardManagementInterface
{
    /**
     * Adds a gift card code to a specified cart.
     *
     * @param string $cartId The cart ID.
     * @param string $code The gift card code data.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified coupon could not be added.
     */
    public function set($cartId, $code);

    /**
     * Deletes a gift card code from a specified cart.
     *
     * @param string $cartId The cart ID.
     * @param string $code The gift card code data.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotDeleteException The specified coupon could not be deleted.
     */
    public function remove($cartId, $code);

    /**
     * Credit amount from a specified cart.
     *
     * @param string $cartId The cart ID.
     * @param double $amount The amount to credit.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function credit($cartId, $amount);

    /**
     * @param string $customerId The customer ID.
     * @param string $code The gift code.
     *
     * @return \Mageplaza\GiftCard\Api\Data\RedeemDetailInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function redeem($customerId, $code);
}
