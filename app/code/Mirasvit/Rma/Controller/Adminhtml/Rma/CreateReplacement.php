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

use Mirasvit\Rma\Api\Repository\RmaRepositoryInterface;
use Mirasvit\Rma\Api\Service\Rma\RmaManagement\CreateReplacementOrderInterface;
use Mirasvit\Rma\Controller\Adminhtml\Rma;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class CreateReplacement extends Rma
{
    /**
     * @var RmaRepositoryInterface
     */
    private $rmaRepository;
    /**
     * @var CreateReplacementOrderInterface
     */
    private $createReplacementOrder;

    /**
     * CreateReplacement constructor.
     * @param RmaRepositoryInterface $rmaRepository
     * @param CreateReplacementOrderInterface $createReplacementOrder
     * @param Context $context
     */
    public function __construct(
        RmaRepositoryInterface $rmaRepository,
        CreateReplacementOrderInterface $createReplacementOrder,
        Context $context
    ) {
        $this->rmaRepository = $rmaRepository;
        $this->createReplacementOrder = $createReplacementOrder;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $rma = $this->rmaRepository->get((int)$this->getRequest()->getParam('rma_id'));
        try {
            $orderId = $this->createReplacementOrder->create($rma);
        } catch (\Exception $e) {
            $this->messageManager->addSuccessMessage($e->getMessage());
            return $resultRedirect->setPath('rma/rma/edit', ['id' => $rma->getId()]);
        }

        return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
    }
}
