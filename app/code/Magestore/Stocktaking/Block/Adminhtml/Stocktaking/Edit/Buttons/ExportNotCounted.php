<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Block\Adminhtml\Stocktaking\Edit\Buttons;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\Stocktaking\Block\Adminhtml\Stocktaking\AbstractStocktaking;

/**
 * Class ExportNotCounted
 *
 * Export not counted product
 */
class ExportNotCounted extends AbstractStocktaking implements ButtonProviderInterface
{
    /**
     * Render button export different list
     *
     * @return array
     */
    public function getButtonData(): array
    {
        $stocktaking = $this->getStocktaking();
        if ($this->request->getParam('id')
            && $stocktaking->getStatus() == StocktakingInterface::STATUS_COUNTING
            && $stocktaking->getStocktakingType() == StocktakingInterface::STOCKTAKING_TYPE_FULL) {
            return [
                'label' => __('Export Not Counted Product'),
                'class' => 'export',
                'on_click' => sprintf(
                    "location.href = '%s';",
                    $this->getUrl(
                        '*/*/exportNotCounted',
                        [
                            'id' => $this->request->getParam('id'),
                            'source_code' => $stocktaking->getSourceCode()
                        ]
                    )
                ),
                'sort_order' => 40,
            ];
        }
        return [];
    }
}
