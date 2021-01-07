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

use Mirasvit\Rma\Api\Data\RmaInterface;

class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface
     */
    private $fieldManagement;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Mirasvit\Rma\Helper\Order\Html
     */
    private $rmaOrderHtml;

    /**
     * @var \Mirasvit\Rma\Helper\Order\Url
     */
    private $orderUrl;

    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
     */
    private $rmaManagement;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;

    /**
     * Info constructor.
     * @param \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $fieldManagement
     * @param \Magento\Framework\Registry $registry
     * @param \Mirasvit\Rma\Helper\Order\Html $rmaOrderHtml
     * @param \Mirasvit\Rma\Helper\Order\Url $orderUrl
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $fieldManagement,
        \Magento\Framework\Registry $registry,
        \Mirasvit\Rma\Helper\Order\Html $rmaOrderHtml,
        \Mirasvit\Rma\Helper\Order\Url $orderUrl,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->fieldManagement = $fieldManagement;
        $this->registry        = $registry;
        $this->rmaOrderHtml    = $rmaOrderHtml;
        $this->orderUrl        = $orderUrl;
        $this->rmaManagement   = $rmaManagement;
        $this->context         = $context;

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
     * @return \Magento\Sales\Api\Data\OrderInterface[]|\Mirasvit\Rma\Model\OfflineOrder[]
     */
    public function getOrders()
    {
        return $this->rmaManagement->getOrders($this->getRma());
    }


    /**
     * @param mixed $order
     * @param bool $orderUrl
     *
     * @return string
     */
    public function getOrderLabel($order, $orderUrl = false)
    {
        if (!$order) {
            $order = $this->rmaManagement->getOrder($this->getRma());
            if (!$order) {
                return __('Removed Order');
            }
        }

        $label = $this->rmaOrderHtml->getOrderLabel($order, $orderUrl);
        if ($order->getIsOffline()) {
            $label = $this->escapeHtml($label);
        }

        return $label;
    }

    /**
     * @param int $orderId
     *
     * @return string|false
     */
    public function getOrderUrl($orderId)
    {
        $order = $this->rmaManagement->getOrder($this->getRma());
        if (!$order || $order->getIsOffline()) {
            return false;
        }

        return $this->orderUrl->getUrl($orderId);
    }

    /**
     * @param RmaInterface $rma
     *
     * @return \Mirasvit\Rma\Api\Data\StatusInterface
     */
    public function getStatus(RmaInterface $rma)
    {
        return $this->rmaManagement->getStatus($rma);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @param bool                                $isEdit
     *
     * @return \Mirasvit\Rma\Model\Field[]
     */
    public function getCustomFields(\Mirasvit\Rma\Api\Data\RmaInterface $rma, $isEdit = false)
    {
        return $this->fieldManagement->getVisibleCustomerCollection($rma->getStatusId(), $isEdit);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     *
     * @return string
     */
    public function getShippingAddressHtml($rma)
    {
        $items   = [];
        $items[] = $rma->getFirstname() . ' ' . $rma->getLastname();
        if ($rma->getEmail()) {
            $items[] = $rma->getEmail();
        }
        if ($rma->getTelephone()) {
            $items[] = $rma->getTelephone();
        }
        if ($rma->getCompany()) {
            $items[] = $rma->getCompany();
        }
        if ($rma->getStreet()) {
            $items[] = $rma->getStreet();
        }
        if ($rma->getCity()) {
            $items[] = $rma->getCity();
        }
        if ($rma->getRegion()) {
            $items[] = $rma->getRegion();
        }
        if ($rma->getPostcode()) {
            $items[] = $rma->getPostcode();
        }
        //@todo fix this
        //        if ($rma->getCountryId()) {
        //            $country = Mage::getModel('directory/country')->loadByCode($rma->getCountryId());
        //            $items[] = $country->getName();
        //        }

        return trim(implode(PHP_EOL, $items));
    }

    /**
     * @param \Magento\Framework\DataObject $rma
     * @param \Mirasvit\Rma\Model\Field     $field
     *
     * @return bool|string
     */
    public function getRmaFieldValue(\Magento\Framework\DataObject $rma, \Mirasvit\Rma\Model\Field $field)
    {
        return $this->fieldManagement->getValue($rma, $field);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     *
     * @return bool|string
     */
    public function getReturnAddressHtml($rma)
    {
        return $this->rmaManagement->getReturnAddressHtml($rma);
    }

}
