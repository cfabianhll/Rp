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

use \Mirasvit\Rma\Api\Repository\ResolutionRepositoryInterface;

abstract class Resolution extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;
    /**
     * @var \Magento\Backend\App\Action\Context
     */
    private $context;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;
    /**
     * @var ResolutionRepositoryInterface
     */
    protected $resolutionRepository;
    /**
     * @var \Mirasvit\Rma\Model\ResolutionFactory
     */
    protected $resolutionFactory;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * Resolution constructor.
     * @param \Mirasvit\Rma\Model\ResolutionFactory $resolutionFactory
     * @param ResolutionRepositoryInterface $resolutionRepository
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Mirasvit\Rma\Model\ResolutionFactory $resolutionFactory,
        ResolutionRepositoryInterface $resolutionRepository,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->resolutionFactory    = $resolutionFactory;
        $this->resolutionRepository = $resolutionRepository;
        $this->escaper              = $escaper;
        $this->localeDate           = $localeDate;
        $this->registry             = $registry;
        $this->context              = $context;
        $this->backendSession       = $context->getSession();
        $this->resultFactory        = $context->getResultFactory();

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
     * @return \Mirasvit\Rma\Model\Resolution
     */
    public function _initResolution()
    {
        $resolution = $this->resolutionFactory->create();
        if ($this->getRequest()->getParam('id')) {
            $resolution->load($this->getRequest()->getParam('id'));
            if ($storeId = (int) $this->getRequest()->getParam('store')) {
                $resolution->setStoreId($storeId);
            }
        }

        $this->registry->register('current_resolution', $resolution);

        return $resolution;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Rma::rma_dictionary_resolution');
    }

    /************************/
}
