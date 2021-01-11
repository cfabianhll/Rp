<?php
/**
 * Copyright © 2017 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Stocktaking\Helper;

use Magestore\Stocktaking\Model\Source\Adminhtml\StocktakingType;
use Magestore\Stocktaking\Model\Source\Adminhtml\Status as StocktakingStatus;

/**
 * Helper Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var StocktakingType
     */
    protected $stocktakingType;

    /**
     * @var StocktakingStatus
     */
    protected $stocktakingStatus;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param StocktakingType $stocktakingType
     * @param StocktakingStatus $stocktakingStatus
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StocktakingType $stocktakingType,
        StocktakingStatus $stocktakingStatus
    ) {
        parent::__construct($context);
        $this->stocktakingType = $stocktakingType;
        $this->stocktakingStatus = $stocktakingStatus;
    }

    /**
     * Get Stocktaking Types
     *
     * @return array
     */
    public function getStocktakingTypes(): array
    {
        return $this->stocktakingType->toOptionHash();
    }

    /**
     * Get Stocktaking Status
     *
     * @return array
     */
    public function getStocktakingStatus(): array
    {
        return $this->stocktakingStatus->toOptionHash();
    }
}
