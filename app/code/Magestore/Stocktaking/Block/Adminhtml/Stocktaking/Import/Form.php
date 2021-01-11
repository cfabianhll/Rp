<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Block\Adminhtml\Stocktaking\Import;

use Magestore\Stocktaking\Service\Adminhtml\Stocktaking\Edit\GetCurrentStocktakingService;

/**
 * Class Form
 *
 * Used to import product
 */
class Form extends \Magento\Backend\Block\Template
{
    /**
     * @var GetCurrentStocktakingService
     */
    protected $getCurrentStocktakingService;

    /**
     * NoticeMessage constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param GetCurrentStocktakingService $getCurrentStocktakingService
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        GetCurrentStocktakingService $getCurrentStocktakingService
    ) {
        $this->getCurrentStocktakingService = $getCurrentStocktakingService;
        parent::__construct($context);
    }

    /**
     * Get csv sample link
     *
     * @return string
     */
    public function getCsvSampleLink()
    {
        return $this->getUrl(
            'stocktaking/stocktaking/downloadSampleCsv',
            [
                'status' => $this->getImportType()
            ]
        );
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return __(
            'Please choose a CSV file to  upload product list with a maximum of 1000 SKUs.'.
                ' You can download this sample CSV file.'
        );
    }

    /**
     * Get import urL
     *
     * @return mixed
     */
    public function getImportLink()
    {
        return $this->getUrl(
            'stocktaking/stocktaking/import',
            [
                'id' => $this->getRequest()->getParam('id'),
                'status' => $this->getImportType()
            ]
        );
    }

    /**
     * Get import title
     *
     * @return string
     */
    public function getTitle()
    {
        return __('Import products');
    }

    /**
     * Get import type
     *
     * @return string|null
     */
    public function getImportType()
    {
        $currentStocktake = $this->getCurrentStocktakingService->getCurrentStocktaking();
        if ($currentStocktake) {
            return $currentStocktake->getStatus();
        }
        return null;
    }
}
