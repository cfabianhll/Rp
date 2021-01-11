<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Service\Adminhtml\Stocktaking\Edit;

use Magestore\Stocktaking\Api\Data\StocktakingInterface;

/**
 * Service to get current stock-taking on Edit page
 */
class GetCurrentStocktakingService
{
    /**
     * @var StocktakingInterface|null
     */
    private $currentStocktaking;

    /**
     * Set Current Stocktaking Model
     *
     * @param StocktakingInterface $stocktaking
     */
    public function setCurrentStocktaking(StocktakingInterface $stocktaking)
    {
        $this->currentStocktaking = $stocktaking;
    }

    /**
     * Get Current Stocktaking Model
     *
     * @return StocktakingInterface|null
     */
    public function getCurrentStocktaking()
    {
        return $this->currentStocktaking;
    }
}
