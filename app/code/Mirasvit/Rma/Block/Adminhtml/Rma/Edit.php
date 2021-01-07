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



namespace Mirasvit\Rma\Block\Adminhtml\Rma;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Mirasvit\Rma\Api\Service\Resolution\ResolutionManagementInterface
     */
    protected $resolutionManagement;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
     */
    protected $rmaManagement;
    /**
     * @var \Mirasvit\Rma\Helper\Rma\Url
     */
    protected $rmaUrl;
    /**
     * @var \Mirasvit\Rma\Helper\Order\Creditmemo
     */
    protected $creditmemoHelper;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    protected $orderInvoiceCollectionFactory;
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;
    /**
     * @var \Magento\Framework\Registry
     */

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $adminSession;
    /**
     * @var \Magento\Framework\Authorization\PolicyInterface
     */
    private $policyInterface;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    private $context;

    /**
     * Edit constructor.
     * @param \Mirasvit\Rma\Api\Service\Resolution\ResolutionManagementInterface $resolutionManagement
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement
     * @param \Mirasvit\Rma\Helper\Rma\Url $rmaUrl
     * @param \Mirasvit\Rma\Helper\Order\Creditmemo $creditmemoHelper
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $orderInvoiceCollectionFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Authorization\PolicyInterface $policyInterface
     * @param \Magento\Backend\Model\Auth\Session $adminSession
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Resolution\ResolutionManagementInterface $resolutionManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Helper\Rma\Url $rmaUrl,
        \Mirasvit\Rma\Helper\Order\Creditmemo $creditmemoHelper,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $orderInvoiceCollectionFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Authorization\PolicyInterface $policyInterface,
        \Magento\Backend\Model\Auth\Session $adminSession,
        array $data = []
    ) {
        $this->resolutionManagement          = $resolutionManagement;
        $this->rmaManagement                 = $rmaManagement;
        $this->rmaUrl                        = $rmaUrl;
        $this->creditmemoHelper              = $creditmemoHelper;
        $this->orderInvoiceCollectionFactory = $orderInvoiceCollectionFactory;
        $this->wysiwygConfig                 = $wysiwygConfig;
        $this->registry                      = $registry;
        $this->context                       = $context;
        $this->policyInterface               = $policyInterface;
        $this->adminSession                  = $adminSession;

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->_objectId   = 'rma_id';
        $this->_controller = 'adminhtml_rma';
        $this->_blockGroup = 'Mirasvit_Rma';

        if (!$this->isDeleteAllowed()) {
            $this->removeButton('delete');
        }

        $this->buttonList->remove('save');

        $this->getToolbar()->addChild(
            'update-split-button',
            'Magento\Backend\Block\Widget\Button\SplitButton',
            [
                'id'           => 'update-split-button',
                'label'        => __('Save'),
                'class_name'   => 'Magento\Backend\Block\Widget\Button\SplitButton',
                'button_class' => 'widget-button-update',
                'options'      => [
                    [
                        'id'             => 'update-button',
                        'label'          => __('Save'),
                        'default'        => true,
                        'data_attribute' => [
                            'mage-init' => [
                                'button' => [
                                    'event'  => 'save',
                                    'target' => '#edit_form',
                                ],
                            ],
                        ],
                    ],
                    [
                        'id'             => 'update-continue-button',
                        'label'          => __('Save & Continue Edit'),
                        'data_attribute' => [
                            'mage-init' => [
                                'button' => [
                                    'event'  => 'saveAndContinueEdit',
                                    'target' => '#edit_form',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $rma = $this->getRma();
        if ($rma) {
            $this->buttonList->add('print', [
                'label'   => __('Print'),
                'onclick' => 'var win = window.open(\'' .
                    $this->rmaUrl->getGuestPrintUrl($rma) . '\', \'_blank\');win.focus();',
            ]);
            $order = $this->rmaManagement->getOrder($rma);
            if ($order && $this->creditmemoHelper->canCreateCreditmemo($rma, $order)) {
                $this->buttonList->add('order_creditmemo_manual', [
                    'label'   => __('Credit Memo'),
                    'onclick' => 'var win = window.open(\'' .
                        $this->creditmemoHelper->getCreditmemoUrl($rma, $order) . '\', \'_blank\');win.focus();',
                ]);
            }

            if ($this->resolutionManagement->isExchangeAllowed($rma)) {
                $this->buttonList->add('order_exchange', [
                    'label'   => __('Exchange Order'),
                    'onclick' => 'var win = window.open(\'' .
                        $this->getCreateOrderUrl($rma) . '\', \'_blank\');win.focus();',
                ]);
            }

            if ($order && !$order->getIsOffline() && $this->resolutionManagement->isReplacementAllowed($rma)) {
                $this->buttonList->add('order_replacement', [
                    'label'    => __('Replacement Order'),
                    'class'    => $rma->getReplacementOrderIds() ? 'disabled' : '',
                    'disabled' => $rma->getReplacementOrderIds(),
                    'onclick'  => 'var win = window.open(\'' .
                        $this->getCreateReplacementOrderUrl($rma) . '\', \'_blank\');win.focus();',
                ]);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    private function isDeleteAllowed()
    {
        $roleId = $this->adminSession->getUser()->getRole()->getRoleId();

        return $this->policyInterface->isAllowed($roleId, 'Mirasvit_Rma::delete');
    }

    /**
     * @return \Mirasvit\Rma\Model\Rma
     */
    public function getRma()
    {
        if ($this->registry->registry('current_rma') && $this->registry->registry('current_rma')->getId()) {
            return $this->registry->registry('current_rma');
        }
    }

    /**
     * @param \Mirasvit\Rma\Model\Rma $rma
     *
     * @return string
     */
    public function getCreateOrderUrl($rma)
    {
        return $this->getUrl(
            'sales/order_create/index/',
            [
                'customer_id' => $rma->getCustomerId(),
                'store_id'    => $rma->getStoreId(),
                'rma_id'      => $rma->getId(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */

    /**
     * @param \Mirasvit\Rma\Model\Rma $rma
     *
     * @return string
     */
    public function getCreateReplacementOrderUrl($rma)
    {
        return $this->getUrl(
            'rma/rma/createReplacement',
            [
                'customer_id' => $rma->getCustomerId(),
                'store_id'    => $rma->getStoreId(),
                'rma_id'      => $rma->getId(),
            ]
        );
    }

    /**
     * @param \Mirasvit\Rma\Model\Rma $rma
     *
     * @return string
     */
    public function getCreditmemoUrl($rma)
    {
        $orderId    = $rma->getOrderId();
        $collection = $this->orderInvoiceCollectionFactory->create()
            ->addFieldToFilter('order_id', $orderId);

        if ($collection->count() == 1) {
            $invoice = $collection->getFirstItem();

            return $this->getUrl(
                'sales/order_creditmemo/new',
                [
                    'order_id'   => $orderId,
                    'invoice_id' => $invoice->getId(),
                    'rma_id'     => $rma->getId(),
                ]
            );
        } else {
            return $this->getUrl(
                'sales/order_creditmemo/new',
                [
                    'order_id' => $orderId,
                    'rma_id'   => $rma->getId()]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderText()
    {
        if ($rma = $this->getRma()) {
            $status = $this->rmaManagement->getStatus($rma);

            return __('RMA #%1 - %2', $this->escapeHtml($rma->getIncrementId()), $this->escapeHtml($status->getName()));
        } else {
            return __('Create New RMA');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->wysiwygConfig->isEnabled()) {
        }
    }
}
