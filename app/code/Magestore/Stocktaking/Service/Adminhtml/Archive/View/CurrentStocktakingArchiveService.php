<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Service\Adminhtml\Archive\View;

use Magento\Backend\Model\Session;
use Magestore\Stocktaking\Api\Data\StocktakingArchiveInterface;

/**
 * Service to get current stock-taking on Edit page
 */
class CurrentStocktakingArchiveService
{
    /**
     * @var StocktakingArchiveInterface|null
     */
    private $currentStocktakingArchive;

    /**
     * Set Current Stocktaking Archive Model
     *
     * @param StocktakingArchiveInterface $stocktakingArchive
     */
    public function setCurrentStocktakingArchive(StocktakingArchiveInterface $stocktakingArchive)
    {
        $this->currentStocktakingArchive = $stocktakingArchive;
    }

    /**
     * Get Current Stocktaking Archive Model
     *
     * @return StocktakingArchiveInterface|null
     */
    public function getCurrentStocktakingArchive()
    {
        return $this->currentStocktakingArchive;
    }
}
