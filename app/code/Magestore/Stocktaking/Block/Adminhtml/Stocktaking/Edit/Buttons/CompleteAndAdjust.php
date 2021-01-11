<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Block\Adminhtml\Stocktaking\Edit\Buttons;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magestore\Stocktaking\Block\Adminhtml\Stocktaking\AbstractStocktaking;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;

/**
 * Complete And Adjust button
 */
class CompleteAndAdjust extends AbstractStocktaking implements ButtonProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getButtonData()
    {
        $stocktaking = $this->getStocktaking();
        if ($this->request->getParam('id')
            && $stocktaking->getStatus() == StocktakingInterface::STATUS_VERIFYING
            && $this->isAllowed('Magestore_Stocktaking::adjust_stock')
        ) {
            $url = $this->getUrl(
                '*/*/complete',
                [
                    '_secure' => true,
                    'id' => $this->request->getParam('id'),
                    'source_code' => $stocktaking->getSourceCode(),
                    'createAdjustStock' => true
                ]
            );

            return [
                'label' => __('Complete & Adjust Stock'),
                'on_click' => sprintf(
                    "deleteConfirm(
                        '%s', 
                        '%s'
                    )",
                    __("Are you sure to complete stock-taking and adjust product’s quantity in source?"),
                    $url
                ),
                'class' => 'complete_and_adjust',
                'sort_order' => 45
            ];
        }
        return [];
    }
}
