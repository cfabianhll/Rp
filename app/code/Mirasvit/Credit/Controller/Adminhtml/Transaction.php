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



namespace Mirasvit\Credit\Controller\Adminhtml;

use Mirasvit\Credit\Helper\Calculation;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Response\Http\FileFactory;

abstract class Transaction extends \Magento\Backend\App\Action
{
    /**
     * @var \Mirasvit\Credit\Model\BalanceFactory
     */
    protected $balanceFactory;

    /**
     * @var \Mirasvit\Credit\Model\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var FileFactory
     */
    protected $fileFactory;
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var Calculation
     */
    protected $calculationHelper;

    /**
     * @param \Mirasvit\Credit\Model\BalanceFactory     $earningFactory
     * @param \Mirasvit\Credit\Model\TransactionFactory $transactionFactory
     * @param Calculation                               $calculationHelper
     * @param \Magento\Framework\Registry               $registry
     * @param FileFactory                               $fileFactory
     * @param CustomerFactory                           $customerFactory
     * @param \Magento\Backend\App\Action\Context       $context
     */
    public function __construct(
        \Mirasvit\Credit\Model\BalanceFactory $earningFactory,
        \Mirasvit\Credit\Model\TransactionFactory $transactionFactory,
        Calculation $calculationHelper,
        \Magento\Framework\Registry $registry,
        FileFactory $fileFactory,
        CustomerFactory $customerFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->balanceFactory = $earningFactory;
        $this->transactionFactory = $transactionFactory;
        $this->calculationHelper = $calculationHelper;
        $this->registry = $registry;
        $this->fileFactory = $fileFactory;
        $this->customerFactory = $customerFactory;
        $this->context = $context;
        $this->backendSession = $context->getSession();
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Mirasvit_Credit::credit_transaction');
        $resultPage->getConfig()->getTitle()->prepend(__('Store Credit'));
        $resultPage->getConfig()->getTitle()->prepend(__('Transactions'));

        return $resultPage;
    }

    /**
     * @return \Mirasvit\Credit\Model\Transaction
     */
    public function initModel()
    {
        $transaction = $this->transactionFactory->create();
        if ($this->getRequest()->getParam('id')) {
            $transaction->load($this->getRequest()->getParam('id'));
        }

        if ($this->getRequest()->getParam('customer_id')) {
            $transaction->setCustomerId($this->getRequest()->getParam('customer_id'));
        }

        $this->registry->register('current_transaction', $transaction);

        return $transaction;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Credit::credit_transaction');
    }
}
