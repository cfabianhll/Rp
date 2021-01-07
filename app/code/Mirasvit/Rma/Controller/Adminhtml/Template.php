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

use Magento\Framework\Escaper;

abstract class Template extends \Magento\Backend\App\Action
{
    /**
     * @var Escaper
     */
    protected $escaper;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Mirasvit\Rma\Model\QuickResponseFactory
     */
    protected $templateFactory;
    /**
     * @var \Magento\Backend\App\Action\Context
     */
    private $context;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * Template constructor.
     * @param \Mirasvit\Rma\Model\QuickResponseFactory $templateFactory
     * @param Escaper $escaper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Mirasvit\Rma\Model\QuickResponseFactory $templateFactory,
        Escaper $escaper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->templateFactory = $templateFactory;
        $this->localeDate      = $localeDate;
        $this->registry        = $registry;
        $this->escaper         = $escaper;
        $this->context         = $context;
        $this->backendSession  = $context->getSession();
        $this->resultFactory   = $context->getResultFactory();

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
     * @return \Mirasvit\Rma\Model\QuickResponse
     */
    public function _initTemplate()
    {
        $template = $this->templateFactory->create();
        if ($this->getRequest()->getParam('id')) {
            $template->load($this->getRequest()->getParam('id'));
        }

        $this->registry->register('current_template', $template);

        return $template;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Rma::rma_dictionary_template');
    }

    /************************/
}
