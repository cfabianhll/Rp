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
 * Complete button
 */
class Complete extends AbstractStocktaking implements ButtonProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getButtonData()
    {
        $stocktaking = $this->getStocktaking();
        if ($this->request->getParam('id')
            && $stocktaking->getStatus() == StocktakingInterface::STATUS_VERIFYING
            && $this->isAllowed('Magestore_Stocktaking::complete')
        ) {
            return [
                'label' => __('Complete'),
                'on_click' => sprintf(
                    "setLocation('%s')",
                    $this->getUrl(
                        '*/*/complete',
                        [
                            '_secure' => true,
                            'id' => $this->request->getParam('id'),
                            'source_code' => $stocktaking->getSourceCode(),
                            'createAdjustStock' => false
                        ]
                    )
                ),
                'class' => 'save primary',
                'sort_order' => 50
            ];
        }
        return [];
    }
}
