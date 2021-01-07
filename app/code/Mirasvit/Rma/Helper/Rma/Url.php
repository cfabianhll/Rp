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



namespace Mirasvit\Rma\Helper\Rma;

class Url extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Backend\Model\Url
     */
    private $backendUrlManager;
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;

    /**
     * Url constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Backend\Model\Url $backendUrlManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\Url $backendUrlManager
    ) {
        $this->context           = $context;
        $this->backendUrlManager = $backendUrlManager;

        parent::__construct($context);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return string
     */
    public function getGuestUrl($rma)
    {
        return $this->_urlBuilder->getUrl('rma/rma/view', ['id' => $rma->getGuestId(), '_scope' => $rma->getStoreId(), '_nosid' => true]);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface|false $order
     * @return string
     */
    public function getCreateUrl($order = false)
    {
        $params = [];
        if ($order) {
            $params['order_id'] = $order->getId();
        }
        $url = $this->_urlBuilder->getUrl('rma/rma/new', $params);

        return $url;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return string
     */
    public function getGuestPrintUrl($rma)
    {
        $url = $this->_urlBuilder->getUrl(
            'rma/rma/print',
            ['id' => $rma->getGuestId(), '_scope' => $rma->getStoreId(), 'rn' => $this->getRand(), '_nosid' => true]
        );

        return $url;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return bool|string
     */
    public function getGuestPrintLabelUrl($rma)
    {
        return $this->_urlBuilder->getUrl(
            'rma/rma/printlabel',
            ['id' => $rma->getGuestId(), '_scope' => $rma->getStoreId(), 'rn' => $this->getRand(), '_nosid' => true]
        );
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return string
     */
    public function getBackendUrl(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        $url = $this->backendUrlManager->getUrl('rma/rma/edit', ['id' => $rma->getId(), '_nosid' => true]);

        return $url;
    }

    /**
     * @return string
     */
    private function getRand()
    {
        return str_replace(['.', ',', ' '], '', microtime());
    }
}