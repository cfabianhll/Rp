<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Stock;

use Amasty\MultiInventory\Model\Export\ConvertToCsv;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\Timezone;

/**
 * Class Render
 */
class GridToCsv extends Action
{
    /**
     * @var ConvertToCsv
     */
    protected $converter;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var Timezone
     */
    private $timezone;

    /**
     * GridToCsv constructor.
     * @param Context $context
     * @param ConvertToCsv $converter
     * @param Timezone $timezone
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        ConvertToCsv $converter,
        Timezone $timezone,
        FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->converter = $converter;
        $this->fileFactory = $fileFactory;
        $this->timezone = $timezone;
    }

    /**
     * Export data provider to CSV
     *
     * @throws LocalizedException
     * @return ResponseInterface
     */
    public function execute()
    {
        $filename = $this->getRequest()->getParam('filename');
        if (!$filename) {
            $filename = 'export_' . $this->timezone->date()->format('Y_m_d_H_i_s');
        }
        $filename .='.csv';

        return $this->fileFactory->create($filename, $this->converter->getFile($filename), DirectoryList::MEDIA);
    }
}
