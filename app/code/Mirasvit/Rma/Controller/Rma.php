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



namespace Mirasvit\Rma\Controller;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

abstract class Rma extends Action
{
    /**
     * List of actions that are allowed for not authorized users.
     *
     * @var string[]
     */
    protected $openActions = [
        'external',
        'postexternal',
        'print',
    ];

    /**
     * @var \Mirasvit\Rma\Helper\Controller\Rma\AbstractStrategy
     */
    protected $strategy;
    /**
     * @var \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory
     */
    private $strategyFactory;
    /**
     * @var Context
     */
    private $context;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Rma constructor.
     * @param \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Context $context
     */
    public function __construct(
        \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory,
        \Magento\Customer\Model\Session $customerSession,
        Context $context
    ) {
        $this->strategyFactory = $strategyFactory;
        $this->customerSession = $customerSession;
        $this->context = $context;
        $this->resultFactory = $context->getResultFactory();

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public abstract function isRequireCustomerAutorization();

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $this->strategy = $this->strategyFactory->create($request);

        if (!$this->getRequest()->isDispatched()) {
            parent::dispatch($request);
        }
        if (!$this->isRequireCustomerAutorization()) {
            return parent::dispatch($request);
        }
        $action = strtolower($this->getRequest()->getActionName());
        $pattern = '/^('.implode('|', $this->openActions).')$/i';

        if (!preg_match($pattern, $action)) {
            if (!$this->customerSession->authenticate()) {
                $this->_actionFlag->set('', 'no-dispatch', true);
            }
        } else {
            $this->customerSession->setNoReferer(true);
        }
        $result = parent::dispatch($request);
        $this->customerSession->unsNoReferer(false);

        return $result;
    }

    /**
     * @param \Magento\Framework\Controller\ResultInterface $resultPage
     * @return void
     */
    protected function initPage(\Magento\Framework\Controller\ResultInterface $resultPage)
    {
        if (!$this->strategy->isRequireCustomerAutorization()) {
            $layout = $resultPage->getLayout();
            $layout->unsetElement('div.sidebar.additional');
            $layout->unsetElement('sidebar.main');
            $pageConfig = $resultPage->getConfig();
            $pageConfig->setPageLayout('1column');
        }

        if ($navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('rma');
        }
    }

}