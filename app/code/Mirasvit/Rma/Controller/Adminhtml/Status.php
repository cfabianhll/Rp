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



namespace Mirasvit\Rma\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mirasvit\Rma\Api\Repository\StatusRepositoryInterface;
use Mirasvit\Rma\Model\StatusFactory;

abstract class Status extends \Magento\Backend\App\Action
{
    /**
     * @var StatusFactory
     */
    protected $statusFactory;
    /**
     * @var StatusRepositoryInterface
     */
    protected $statusRepository;
    /**
     * @var TimezoneInterface
     */
    protected $localeDate;
    /**
     * @var Escaper
     */
    protected $escaper;
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var \Mirasvit\Rma\Api\Config\RmaConfigInterface
     */
    private $rmaConfig;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * Status constructor.
     * @param \Mirasvit\Rma\Api\Config\RmaConfigInterface $rmaConfig
     * @param StatusFactory $statusFactory
     * @param StatusRepositoryInterface $statusRepository
     * @param TimezoneInterface $localeDate
     * @param Escaper $escaper
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        \Mirasvit\Rma\Api\Config\RmaConfigInterface $rmaConfig,
        StatusFactory $statusFactory,
        StatusRepositoryInterface $statusRepository,
        TimezoneInterface $localeDate,
        Escaper $escaper,
        Registry $registry,
        Context $context
    ) {
        $this->rmaConfig        = $rmaConfig;
        $this->statusFactory    = $statusFactory;
        $this->statusRepository = $statusRepository;
        $this->localeDate       = $localeDate;
        $this->escaper          = $escaper;
        $this->registry         = $registry;
        $this->context          = $context;
        $this->backendSession   = $context->getSession();
        $this->resultFactory    = $context->getResultFactory();

        parent::__construct($context);
    }

    /**
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Magento_Sales::sales_operation');
        $resultPage->getConfig()->getTitle()->prepend(__('RMA'));
        return $resultPage;
    }

    /**
     * @return \Mirasvit\Rma\Model\Status
     */
    public function _initStatus()
    {
        $status = $this->statusFactory->create();
        if ($this->getRequest()->getParam('id')) {
            $status->load($this->getRequest()->getParam('id'));
            if ($storeId = (int) $this->getRequest()->getParam('store')) {
                $status->setStoreId($storeId);
            }
        }

        $this->registry->register('current_status', $status);

        return $status;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Rma::rma_dictionary_status');
    }

    /************************/
}
