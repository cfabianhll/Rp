<?php

/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\Source\Adminhtml;

use Magestore\Stocktaking\Api\Data\StocktakingInterface;

/**
 * Class StocktakingType
 *
 * Used for stocktaking type
 */
class StocktakingType implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Get to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => StocktakingInterface::STOCKTAKING_TYPE_PARTIAL, 'label' => __('Partial')],
            ['value' => StocktakingInterface::STOCKTAKING_TYPE_FULL, 'label' => __('Full')]
        ];
    }

    /**
     * Return array of options as key-value pairs.
     *
     * @return array Format: array('<key>' => '<value>', '<key>' => '<value>', ...)
     */
    public function toOptionHash()
    {
        $option = [
            StocktakingInterface::STOCKTAKING_TYPE_PARTIAL => __('Partial'),
            StocktakingInterface::STOCKTAKING_TYPE_FULL => __('Full'),
        ];

        return $option;
    }
}
