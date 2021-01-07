<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column;

use Amasty\MultiInventory\Api\ExportRepositoryInterface;
use Amasty\MultiInventory\Model\Export;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ExportActions
 */
class ExportActions extends Column
{
    /**
     * Url path
     */
    const URL_PATH_DELETE = 'amasty_multi_inventory/export/delete';

    const URL_PATH_DOWNLOAD = 'amasty_multi_inventory/export/download';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ExportRepositoryInterface
     */
    private $repository;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var File
     */
    private $file;

    /**
     * ExportActions constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $fileSystem
     * @param File $file
     * @param ExportRepositoryInterface $repository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        Filesystem $fileSystem,
        File $file,
        ExportRepositoryInterface $repository,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->repository = $repository;
        $this->fileSystem = $fileSystem;
        $this->file = $file;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['export_id'])) {
                    $export = $this->repository->getById($item['export_id']);
                    $path = $path = $this->fileSystem
                            ->getDirectoryWrite(DirectoryList::MEDIA)
                            ->getAbsolutePath('/') . Export::PATH_EXPORT . $export->getFile();
                    if ($this->file->fileExists($path)) {
                        $url = $this->storeManager
                            ->getStore()
                            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

                        $item[$this->getData('name')]['download'] = [
                            'href' => $this->urlBuilder->getUrl(
                                $url
                                . Export::PATH_EXPORT
                                . $export->getFile()
                            ),
                            'label' => __('Download'),
                        ];
                    }

                    $item[$this->getData('name')]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            static::URL_PATH_DELETE,
                            [
                                'export_id' => $item['export_id']
                            ]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete "${ $.$data.title }"'),
                            'message' => __('Are you sure you want to delete a "${ $.$data.file }" record?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
