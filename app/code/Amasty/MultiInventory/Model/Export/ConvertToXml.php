<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Export;

use Amasty\MultiInventory\Api\ExportRepositoryInterface;
use Amasty\MultiInventory\Model\ExportFactory;
use Amasty\MultiInventory\Ui\Component\MassAction\FileFilter;
use Magento\Framework\Convert\ExcelFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Ui\Model\Export\SearchResultIteratorFactory;

/**
 * Class ConvertToXml
 */
class ConvertToXml extends Convert
{
    protected $metadataProvider;

    /**
     * @var ExcelFactory
     */
    protected $excelFactory;

    /**
     * @var SearchResultIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * ConvertToXml constructor.
     *
     * @param Filesystem $filesystem
     * @param FileFilter $filter
     * @param ExportFactory $factory
     * @param ExportRepositoryInterface $repository
     * @param ExcelFactory $excelFactory
     * @param SearchResultIteratorFactory $iteratorFactory
     *
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        FileFilter $filter,
        ExportFactory $factory,
        ExportRepositoryInterface $repository,
        ExcelFactory $excelFactory,
        SearchResultIteratorFactory $iteratorFactory
    ) {
        $this->excelFactory = $excelFactory;
        $this->iteratorFactory = $iteratorFactory;
        parent::__construct($filesystem, $filter, $factory, $repository);
    }

    /**
     * @param $filename
     *
     * @return array
     * @throws CouldNotSaveException
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function getFile($filename)
    {
        $file = \Amasty\MultiInventory\Model\Export::PATH_EXPORT . $filename;
        $this->directory->create('amasty_export_stock');
        $headers = $this->factory->create()->getHeaders();
        $searchResultItems = $this->getItems();

        $searchResultIterator = $this->iteratorFactory->create(['items' => $searchResultItems]);

        $excel = $this->excelFactory->create([
            'iterator' => $searchResultIterator,
            'rowCallback' => [$this, 'getRowData'],
        ]);

        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();

        $excel->setDataHeader($headers);
        $excel->write($stream, $filename);

        $stream->unlock();
        $stream->close();
        $this->saveFile($filename);

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => false
        ];
    }
}
