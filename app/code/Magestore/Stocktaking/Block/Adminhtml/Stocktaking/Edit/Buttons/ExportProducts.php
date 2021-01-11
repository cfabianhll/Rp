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
 * Export Products button
 */
class ExportProducts extends AbstractStocktaking implements ButtonProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getButtonData()
    {
        $stocktaking = $this->getStocktaking();
        if ($this->request->getParam('id')
            && $stocktaking->getStatus() != StocktakingInterface::STATUS_VERIFYING
        ) {

            return [
                'label' => __('Export Products'),
                'on_click' => sprintf(
                    "setLocation('%s')",
                    $this->getUrl('*/*/exportProducts', ['_secure' => true, 'id' => $this->request->getParam('id')])
                ),
                'class' => 'export',
                'sort_order' => 30
            ];
        }
        return [];
    }
}
