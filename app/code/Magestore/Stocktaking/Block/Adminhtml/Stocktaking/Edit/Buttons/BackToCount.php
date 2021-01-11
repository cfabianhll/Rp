<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Block\Adminhtml\Stocktaking\Edit\Buttons;

use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\Stocktaking\Block\Adminhtml\Stocktaking\AbstractStocktaking;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Back To Count button
 */
class BackToCount extends AbstractStocktaking implements ButtonProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getButtonData()
    {
        $stocktaking = $this->getStocktaking();
        if ($this->request->getParam('id')
            && $stocktaking->getStatus() == StocktakingInterface::STATUS_VERIFYING
        ) {

            return [
                'label' => __('Back to Count'),
                'on_click' => sprintf(
                    "setLocation('%s')",
                    $this->getUrl('*/*/backToCount', ['_secure' => true, 'id' => $this->request->getParam('id')])
                ),
                'class' => 'back_to_count',
                'sort_order' => 20
            ];
        }
        return [];
    }
}
