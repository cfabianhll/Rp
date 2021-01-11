<?php

/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\Import;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\Stocktaking\Api\StocktakingItemRepositoryInterface;
use Magestore\Stocktaking\Api\StocktakingRepositoryInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking as StocktakingResource;
use Psr\Log\LoggerInterface;

/**
 * Class CsvImportHandler
 *
 * Csv import handler for stocktaking
 */
class CsvImportHandler
{
    /**
     * @var CsvFileProcessor
     */
    protected $csvFileProcessor;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var StocktakingRepositoryInterface
     */
    protected $stocktakingRepository;

    /**
     * @var StocktakingItemRepositoryInterface
     */
    protected $stocktakingItemRepository;

    /**
     * @var StocktakingResource
     */
    protected $stocktakingResource;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CsvImportHandler constructor.
     *
     * @param CsvFileProcessor $csvFileProcessor
     * @param ProductCollectionFactory $productCollectionFactory
     * @param StocktakingRepositoryInterface $stocktakingRepository
     * @param StocktakingItemRepositoryInterface $stocktakingItemRepository
     * @param StocktakingResource $stocktakingResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        CsvFileProcessor $csvFileProcessor,
        ProductCollectionFactory $productCollectionFactory,
        StocktakingRepositoryInterface $stocktakingRepository,
        StocktakingItemRepositoryInterface $stocktakingItemRepository,
        StocktakingResource $stocktakingResource,
        LoggerInterface $logger
    ) {
        $this->csvFileProcessor = $csvFileProcessor;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->stocktakingItemRepository = $stocktakingItemRepository;
        $this->stocktakingRepository = $stocktakingRepository;
        $this->stocktakingResource = $stocktakingResource;
        $this->logger = $logger;
    }

    /**
     * Import from CSV File
     *
     * @param array $file
     * @param int $stocktakeId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importFromCsvFile(array $file, int $stocktakeId)
    {
        if (!isset($file['tmp_name'])) {
            throw new LocalizedException(__('Invalid file upload attempt'));
        }
        $importProductRawData = $this->csvFileProcessor->readFile($file['tmp_name']);
        $fileFields = $importProductRawData[0];
        /* @var \Magestore\Stocktaking\Model\Stocktaking $stocktaking */
        $stocktaking = $this->stocktakingRepository->load($stocktakeId);
        $countSuccess = 0;
        if ($stocktaking->getId()) {
            $validFields = $this->_filterFileFields($fileFields, $stocktaking->getStatus());
            $invalidFields = array_diff_key($fileFields, $validFields);
            $importProductData = $this->_filterImportProductData($importProductRawData, $invalidFields, $validFields);
            $countSuccess = $this->getDataFromFile(
                $this->standardizeCsv($importProductData, $stocktaking->getStatus()),
                $stocktaking
            );
        }
        return $countSuccess;
    }

    /**
     * Get data from file
     *
     * @param array $importProductData
     * @param StocktakingInterface $stocktaking
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function getDataFromFile(array $importProductData, StocktakingInterface $stocktaking)
    {
        $invalidData = [];
        $countSuccess = 0;
        foreach ($importProductData as $rowIndex => $dataRow) {
            if ($rowIndex == 0) {
                continue;
            }
            $result = $this->processRowData($dataRow, $stocktaking, $invalidData, $countSuccess);
            $invalidData = $result['invalidData'];
            $countSuccess = $result['countSuccess'];
        }
        return [
            'invalidData' => $invalidData,
            'countSuccess' => $countSuccess
        ];
    }

    /**
     * Process row data and return invalid
     *
     * @param array $row
     * @param StocktakingInterface $stocktaking
     * @param array $invalidData
     * @param int $countSuccess
     * @return array
     */
    protected function processRowData(
        array $row,
        StocktakingInterface $stocktaking,
        array $invalidData,
        int $countSuccess
    ) {
        $isInvalid = false;
        $productSku = $row;
        $productCollection = $this->productCollectionFactory->create()->addAttributeToSelect('name');
        $productCollection->getSelect()->joinInner(
            ['source_item' => $this->stocktakingResource->getTable('inventory_source_item')],
            'e.sku = source_item.sku AND source_item.source_code = "' . $stocktaking->getSourceCode() . '"',
            ['source_item.quantity']
        );
        $productCollection->addFieldToFilter('sku', $productSku);
        if ($productCollection->getSize()) {
            /* @var ProductModel $productModel */
            $productModel = $productCollection->getFirstItem();
            $importResult = $this->stocktakingItemRepository->importData($productModel, $row, $stocktaking);
            if ($importResult) {
                $countSuccess++;
            }
        } else {
            $isInvalid = true;
            $row[] = __('Sku has been not found in source');
        }

        if ($stocktaking->getStatus() != StocktakingInterface::STATUS_PREPARING) {
            if ($row[1]) {
                if (!is_numeric($row[1])) {
                    $isInvalid = true;
                    $row[] = __('Quantity must be a number');
                }
            } else {
                $isInvalid = true;
                $row[] = __('Quantity must be not empty');
            }
        }

        if ($isInvalid) {
            $invalidData[] = $row;
        }
        return [
            'invalidData' => $invalidData,
            'countSuccess' => $countSuccess
        ];
    }

    /**
     * Filter file field
     *
     * @param array $fileFields
     * @param int $status
     * @return array
     */
    protected function _filterFileFields(array $fileFields, int $status)
    {
        $filteredFields = $this->getRequiredCsvFields($status);
        $requiredFieldsNum = count($this->getRequiredCsvFields($status));
        $fileFieldsNum = count($fileFields);
        for ($index = $requiredFieldsNum; $index < $fileFieldsNum; $index++) {
            $titleFieldName = $fileFields[$index];
            $filteredFields[$index] = $titleFieldName;
        }
        return $filteredFields;
    }

    /**
     * Standard csv file
     *
     * @param array $data
     * @param int $status
     * @return array
     */
    public function standardizeCsv(array $data, int $status)
    {
        $columnCounted = count($this->getRequiredCsvFields($status));
        $standardData = [];
        foreach ($data as $dataRow) {
            $standardRow = [];
            for ($index = 0; $index < $columnCounted; $index++) {
                if (!isset($dataRow[$index])) {
                    $standardRow[$index] = '';
                } else {
                    $standardRow[$index] = $dataRow[$index];
                }
            }
            $standardData[] = $standardRow;
        }
        return $standardData;
    }

    /**
     * Download invalid stock-take csv by id
     *
     * @param int $stocktakeId
     * @param array $invalidStocktakingData
     * @return \Magento\Framework\App\ResponseInterface|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function downloadInvalidStocktakingCsvFile(int $stocktakeId, array $invalidStocktakingData)
    {
        $stockTake = $this->stocktakingRepository->load($stocktakeId);
        $downloadFileName = 'import_product_stocktake_invalid';
        $name = $downloadFileName . sha1(microtime());
        $stream = $this->csvFileProcessor->createFile($name);
        $stream->lock();

        $stream->writeCsv($this->getRequiredCsvFields($stockTake->getStatus()));

        foreach ($invalidStocktakingData as $row) {
            $stream->writeCsv($row);
        }
        $stream->unlock();
        $stream->close();

        try {
            return $this->csvFileProcessor->createDownloadFile($downloadFileName, $name);
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get required csv fields
     *
     * @param int $status
     * @return array
     */
    public function getRequiredCsvFields(int $status)
    {
        if ($status == StocktakingInterface::STATUS_PREPARING) {
            return [
                __('SKU')
            ];
        } else {
            return [
                __('SKU'),
                __('COUNTED QUANTITY'),
                __('REASON OF DIFFERENCE')
            ];
        }
    }

    /**
     * Filter import product data
     *
     * @param array $productRawData
     * @param array $invalidFields
     * @param array $validFields
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _filterImportProductData(array $productRawData, array $invalidFields, array $validFields)
    {
        $validFieldsNum = count($validFields);
        foreach ($productRawData as $rowIndex => $dataRow) {
            // unset invalid fields from data row
            $arrayIndex = array_keys($dataRow);
            foreach ($arrayIndex as $fieldIndex) {
                if (isset($invalidFields[$fieldIndex])) {
                    unset($productRawData[$rowIndex][$fieldIndex]);
                }
            }
            // check if number of fields in row match with number of valid fields
            if (count($productRawData[$rowIndex]) != $validFieldsNum) {
                throw new LocalizedException(__('Invalid file format.'));
            }
        }
        return $productRawData;
    }
}
