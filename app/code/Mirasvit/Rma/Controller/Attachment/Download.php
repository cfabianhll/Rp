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



namespace Mirasvit\Rma\Controller\Attachment;

use Magento\Framework\Controller\ResultFactory;

class Download extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Mirasvit\Rma\Api\Repository\AttachmentRepositoryInterface
     */
    private $attachmentRepository;
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $context;

    /**
     * Download constructor.
     * @param \Mirasvit\Rma\Api\Repository\AttachmentRepositoryInterface $attachmentRepository
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Mirasvit\Rma\Api\Repository\AttachmentRepositoryInterface $attachmentRepository,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->attachmentRepository = $attachmentRepository;
        $this->context = $context;
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }


    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        $uid = $this->getRequest()->getParam('uid');
        try {
            $attachment = $this->attachmentRepository->getByUid($uid);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $resultPage->setContents('wrong URL');
        }

        // give our picture the proper headers...otherwise our page will be confused
        $resultPage->setHeader("Content-Disposition", "attachment; filename={$attachment->getName()}");
        $resultPage->setHeader("Content-length", $attachment->getSize());
        $resultPage->setHeader("Content-type", $attachment->getType());
        $resultPage->setContents($attachment->getBody());
        return $resultPage;
    }
}
