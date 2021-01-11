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
use Magestore\Stocktaking\Controller\Adminhtml\Stocktaking\Update as UpdateController;

/**
 * Class Back To Prepare Button
 */
class BackToPrepare extends AbstractStocktaking implements ButtonProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getButtonData(): array
    {
        $stocktaking = $this->getStocktaking();
        if ($this->request->getParam('id')
            && $stocktaking
            && $stocktaking->getStatus() == StocktakingInterface::STATUS_COUNTING
            && $stocktaking->getStocktakingType() == StocktakingInterface::STOCKTAKING_TYPE_PARTIAL
        ) {
            return [
                'label' => __('Back to Prepare'),
                'class' => 'back_to_prepare',
                'on_click' => '',
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'ms_stocktaking_edit_form.ms_stocktaking_edit_form',
                                    'actionName' => 'save',
                                    'params' => [
                                        true,
                                        [
                                            'type' => UpdateController::ACTION_TYPE_BACK_TO_PREPARE
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                ],
                'sort_order' => 15,
            ];
        }
        return [];
    }
}
