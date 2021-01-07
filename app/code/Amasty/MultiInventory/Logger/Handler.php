<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Logger;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Stdlib\DateTime\Timezone;

/**
 * Class Handler
 */
class Handler extends Base
{
    protected $loggerType = Logger::INFO;

    /**
     * Handler constructor.
     *
     * @param DriverInterface $filesystem
     * @param Timezone $timezone
     * @param string|null $filePath
     *
     * @throws \Exception
     */
    public function __construct(
        DriverInterface $filesystem,
        Timezone $timezone,
        $filePath = null
    ) {
        $date = $timezone->date();
        $this->fileName = '/var/log/Amasty-inventory-' . $date->format('y-m-d') . '.log';
        parent::__construct($filesystem, $filePath);
    }
}
