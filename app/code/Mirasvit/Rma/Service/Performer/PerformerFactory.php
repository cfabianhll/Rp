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


namespace Mirasvit\Rma\Service\Performer;


class PerformerFactory implements \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface
{
    /**
     * @var GuestStrategy
     */
    private $guestStrategy;
    /**
     * @var CustomerStrategy
     */
    private $customerStrategy;

    /**
     * PerformerFactory constructor.
     * @param CustomerStrategy $customerStrategy
     * @param GuestStrategy $guestStrategy
     */
    public function __construct(
        CustomerStrategy $customerStrategy,
        GuestStrategy $guestStrategy
    ) {
        $this->guestStrategy    = $guestStrategy;
        $this->customerStrategy = $customerStrategy;
    }

    /**
     * @param string                                                    $type
     * @param \Magento\User\Model\User|\Magento\Customer\Model\Customer $performer
     * @return \Mirasvit\Rma\Api\Service\Performer\PerformerInterface
     */
    public function create($type, $performer) 
    {
        $strategy = null;
        switch ($type) {
            case self::CUSTOMER:
                $strategy = $this->customerStrategy;
                break;
            case self::USER:
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $strategy = $objectManager->create('\Mirasvit\Rma\Service\Performer\UserStrategy');
                break;
            case self::GUEST:
                $strategy = $this->guestStrategy;
                break;
            default:
                trigger_error("Invalid perfomer type");
        }
        $strategy->setPerfomer($performer);
        return $strategy;
    }

}