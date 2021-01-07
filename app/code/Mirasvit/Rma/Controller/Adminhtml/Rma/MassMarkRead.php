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
use Magento\Framework\Exception\LocalizedException;
use Mirasvit\Rma\Controller\Adminhtml\Rma;
use Magento\Ui\Component\MassAction\Filter;
use Mirasvit\Rma\Model\ResourceModel\Rma\CollectionFactory;

class MassMarkRead extends Rma
{
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \Mirasvit\Rma\Model\RmaFactory
     */
    private $rmaFactory;

    /**
     * MassMarkRead constructor.
     * @param \Mirasvit\Rma\Model\RmaFactory $rmaFactory
     * @param \Magento\Backend\App\Action\Context $context
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     */
    public function __construct(
        \Mirasvit\Rma\Model\RmaFactory $rmaFactory,
        \Magento\Backend\App\Action\Context $context,
        CollectionFactory $collectionFactory,
        Filter $filter
    ) {
        $this->rmaFactory = $rmaFactory;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $rma) {
            $rma->setIsAdminRead(($this->getRequest()->getParam('is_read')));
            $rma->save();
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been changed.', $collectionSize));
        return $resultRedirect->setPath('*/*/index');
    }
}
