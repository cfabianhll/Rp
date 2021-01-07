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



namespace Mirasvit\Rma\Controller\Adminhtml\Rma;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Escaper;
use Mirasvit\Rma\Controller\Adminhtml\Rma;

class Edit extends Rma
{
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface
     */
    private $rmaRepository;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SaveInterface
     */
    private $rmaSaveManagement;

    /**
     * Edit constructor.
     * @param \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SaveInterface $rmaSaveManagement
     * @param Escaper $escaper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SaveInterface $rmaSaveManagement,
        Escaper $escaper,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->escaper           = $escaper;
        $this->registry          = $registry;
        $this->rmaRepository     = $rmaRepository;
        $this->rmaSaveManagement = $rmaSaveManagement;

        parent::__construct($context);
    }


    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        try {
            $rma = $this->rmaRepository->get($this->getRequest()->getParam('id'));
            $this->registry->register('current_rma', $rma);
            $incrementId = $this->escaper->escapeHtml($rma->getIncrementId());
            $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('RMA #%1', $incrementId));
            $this->rmaSaveManagement->markAsReadForUser($rma);

            $this->_addContent($resultPage->getLayout()->createBlock('\Mirasvit\Rma\Block\Adminhtml\Rma\Edit'));
            return $resultPage;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addError(__('The rma does not exist.'));
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('*/*/');
        }
    }
}
