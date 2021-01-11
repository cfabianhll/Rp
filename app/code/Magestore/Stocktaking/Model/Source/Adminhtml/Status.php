<?php

/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\Source\Adminhtml;

use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class Status
 *
 * Used for status
 */
class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Status constructor.
     *
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Get to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->getActionUrlPath() == 'stocktaking_stocktaking_index') {
            return $this->getActiveOptions();
        } elseif ($this->getActionUrlPath() == 'stocktaking_archive_index') {
            return $this->getArchiveOptions();
        } else {
            return array_merge($this->getActiveOptions(), $this->getArchiveOptions());
        }
    }

    /**
     * Get active options
     *
     * @return array
     */
    public function getActiveOptions()
    {
        return [
            ['value' => StocktakingInterface::STATUS_PREPARING, 'label' => __('Preparing')],
            ['value' => StocktakingInterface::STATUS_COUNTING, 'label' => __('Counting')],
            ['value' => StocktakingInterface::STATUS_VERIFYING, 'label' => __('Verifying')]
        ];
    }

    /**
     * Get archive options
     *
     * @return array
     */
    public function getArchiveOptions()
    {
        return [
            ['value' => StocktakingInterface::STATUS_CANCELED, 'label' => __('Canceled')],
            ['value' => StocktakingInterface::STATUS_COMPLETED, 'label' => __('Completed')]
        ];
    }

    /**
     * Get action url path
     *
     * @return string
     */
    public function getActionUrlPath()
    {
        return $this->request->getFullActionName();
    }

    /**
     * Return array of options as key-value pairs.
     *
     * @return array Format: array('<key>' => '<value>', '<key>' => '<value>', ...)
     */
    public function toOptionHash()
    {
        $option = [
            StocktakingInterface::STATUS_NEW => __('New'),
            StocktakingInterface::STATUS_PREPARING => __('Preparing'),
            StocktakingInterface::STATUS_COUNTING => __('Counting'),
            StocktakingInterface::STATUS_VERIFYING => __('Verifying'),
            StocktakingInterface::STATUS_CANCELED => __('Canceled'),
            StocktakingInterface::STATUS_COMPLETED => __('Completed'),
        ];

        return $option;
    }
}
