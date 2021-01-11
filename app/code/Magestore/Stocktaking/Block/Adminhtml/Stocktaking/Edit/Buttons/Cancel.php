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
 * Cancel button
 */
class Cancel extends AbstractStocktaking implements ButtonProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getButtonData()
    {
        $stocktaking = $this->getStocktaking();
        if ($this->request->getParam('id')
            && $stocktaking->getStatus() != StocktakingInterface::STATUS_VERIFYING
            && $this->isAllowed('Magestore_Stocktaking::cancel')
        ) {
            $url = $this->getUrl(
                '*/*/cancel',
                ['_secure' => true, 'id' => $this->request->getParam('id')]
            );

            return [
                'label' => __('Cancel'),
                'on_click' => sprintf(
                    "deleteConfirm(
                        '%s', 
                        '%s'
                    )",
                    __("Are you sure to cancel this stock-taking?"),
                    $url
                ),
                'class' => 'cancel',
                'sort_order' => 20
            ];
        }
        return [];
    }
}
