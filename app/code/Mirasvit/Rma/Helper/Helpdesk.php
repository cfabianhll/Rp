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


namespace Mirasvit\Rma\Helper;

class Helpdesk extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;

    /**
     * Helpdesk constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->context       = $context;
        $this->moduleManager = $context->getModuleManager();

        parent::__construct($context);
    }

    /**
     * @param int $ticketId
     * @return bool|\Mirasvit\Helpdesk\Model\Ticket
     */
    public function getTicket($ticketId)
    {
        if (!$this->moduleManager->isEnabled('Mirasvit_Rma')) {
            return false;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        return $objectManager->create('Mirasvit\Helpdesk\Model\Ticket')->load($ticketId);
    }
}
