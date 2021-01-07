<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Export;

use Amasty\MultiInventory\Api\ExportRepositoryInterface;
use Amasty\MultiInventory\Model\ExportFactory;
use Amasty\MultiInventory\Traits\Additional;
use Amasty\MultiInventory\Ui\Component\MassAction\FileFilter as Filter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Class ConvertToCsv
 */
abstract class Convert
{
    use Additional;

    /**
     * @var WriteInterface
     */
    public $directory;

    /**
     * @var ExportFactory
     */
    public $factory;

    /**
     * @var ExportRepositoryInterface
     */
    private $repository;

    /**
     * ConvertToCsv constructor.
     *
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param ExportFactory $factory
     * @param ExportRepositoryInterface $repository
     *
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        ExportFactory $factory,
        ExportRepositoryInterface $repository
    ) {
        $this->filter = $filter;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->factory = $factory;
        $this->repository = $repository;
    }

    abstract public function getFile($filename);

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getItems()
    {
        $component = $this->filter->getComponent();
        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();

        return $dataProvider->getSqlItems();
    }

    /**
     * @param $filename
     *
     * @throws CouldNotSaveException
     */
    public function saveFile($filename)
    {
        $export = $this->factory->create();
        $export->setFile($filename);
        $this->repository->save($export);
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function getRowData($data)
    {
        $headers = $this->factory->create()->getHeaders();
        $arraySend = [];
        foreach ($headers as $field) {
            $arraySend[] = $data[$field];
        }

        return $arraySend;
    }
}
