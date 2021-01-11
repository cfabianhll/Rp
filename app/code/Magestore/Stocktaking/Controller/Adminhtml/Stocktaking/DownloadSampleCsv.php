<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Controller\Adminhtml\Stocktaking;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;

/**
 * Class DownloadSampleCsv
 *
 * Used for download sample csv
 */
class DownloadSampleCsv extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * @var \Magestore\Stocktaking\Model\Import\GenerateSampleCsv
     */
    protected $generateSampleCsv;

    /**
     * DownloadSampleCsv constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magestore\Stocktaking\Model\Import\GenerateSampleCsv $generateSampleCsv
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magestore\Stocktaking\Model\Import\GenerateSampleCsv $generateSampleCsv
    ) {
        $this->generateSampleCsv = $generateSampleCsv;
        parent::__construct($context);
    }

    /**
     * Download file
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        if ((int) $this->getRequest()->getParam('status') == StocktakingInterface::STATUS_PREPARING) {
            return $this->generateSampleCsv->generateStockTakePrepareFile();
        } else {
            return $this->generateSampleCsv->generateStockTakeCountedFile();
        }
    }
}
