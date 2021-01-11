<?php

/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Storepickup
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Storepickup\Controller\Adminhtml\Schedule;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Model\Export\ConvertToCsv;
use Magestore\Storepickup\Controller\Adminhtml\AbstractExportAction;

/**
 * Schedule - Export csv controller
 */
class ExportCsv extends AbstractExportAction implements HttpGetActionInterface
{
    /**
     * @var ConvertToCsv
     */
    protected $converter;

    /**
     * ExportCsv constructor.
     *
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param ConvertToCsv $converter
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        ConvertToCsv $converter
    ) {
        parent::__construct($context, $fileFactory);
        $this->converter = $converter;
    }

    /**
     * File name to export.
     *
     * @return string
     */
    public function _getFileName()
    {
        return 'schedules.csv';
    }

    /**
     * Get Content
     *
     * @return array|string
     * @throws LocalizedException
     */
    public function _getContent()
    {
        return $this->converter->getCsvFile();
    }

    /**
     * Check the permission to run it.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Storepickup::schedule');
    }
}
