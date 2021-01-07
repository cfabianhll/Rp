<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.1.12
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Rma\Service\Notification;


class NotificationFactory implements \Mirasvit\Rma\Api\Service\Notification\NotificationFactoryInterface
{
    /**
     * @var RmaStrategy
     */
    private $rmaStrategy;

    /**
     * NotificationFactory constructor.
     * @param RmaStrategy $rmaStrategy
     */
    public function __construct(
        RmaStrategy $rmaStrategy
    ) {
        $this->rmaStrategy = $rmaStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function create($type)
    {
        $strategy = null;
        switch ($type) {
            case self::RMA:
                $strategy = $this->rmaStrategy;
                break;
            default:
                trigger_error("Invalid perfomer type");
        }

        return $strategy;
    }

}