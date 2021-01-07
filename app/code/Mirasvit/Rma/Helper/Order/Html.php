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



namespace Mirasvit\Rma\Helper\Order;

/**
 * Helper which creates different html code
 */
class Html extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;

    /**
     * Html constructor.
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->localeDate = $localeDate;
        $this->context = $context;

        parent::__construct($context);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param bool|false $url
     * @return string
     */
    public function getOrderLabel($order, $url = false)
    {
        if ($order->getIsOffline()) {
            $res = $this->getOfflineLabel($order);
        } else {
            $res = $this->getLabel($order, $url);
        }

        return $res;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param bool|false $url
     * @return string
     */
    private function getLabel($order, $url = false)
    {
        $res = "#{$order->getRealorderId()}";
        if ($url) {
            $res = "<a href='{$url}' target='_blank'>$res</a>";
        }
        $res .= __(
            ' at %1 (%2)',
            $this->localeDate->formatDate($order->getCreatedAt(), \IntlDateFormatter::MEDIUM),
            strip_tags($order->formatPrice($order->getGrandTotal()))
        );

        return $res;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\OfflineOrderInterface $order
     * @param bool|false $url
     * @return string
     */
    private function getOfflineLabel($order, $url = false)
    {
        $res = $order->getReceiptNumber();
        if ($url) {
            $res = "<a href='{$url}' target='_blank'>$res</a>";
        }
        $res .= __(' at %1', $this->localeDate->formatDate($order->getCreatedAt(), \IntlDateFormatter::MEDIUM));

        return $res;
    }
}