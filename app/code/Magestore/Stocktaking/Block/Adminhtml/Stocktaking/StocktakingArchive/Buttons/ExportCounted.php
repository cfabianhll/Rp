<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Block\Adminhtml\Stocktaking\StocktakingArchive\Buttons;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magestore\Stocktaking\Block\Adminhtml\Stocktaking\AbstractStocktaking;

/**
 * Button Export counted product
 */
class ExportCounted extends AbstractStocktaking implements ButtonProviderInterface
{
    /**
     * Render button export different list
     *
     * @return array
     */
    public function getButtonData(): array
    {
        if ($this->request->getParam('id')) {
            return [
                'label' => __('Export Counted List'),
                'class' => 'export',
                'on_click' => sprintf(
                    "location.href = '%s';",
                    $this->getUrl(
                        '*/*/exportCounted',
                        [
                            'id' => $this->request->getParam('id')
                        ]
                    )
                ),
                'sort_order' => 50,
            ];
        }
        return [];
    }
}
