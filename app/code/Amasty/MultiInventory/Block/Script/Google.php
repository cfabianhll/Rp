<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Script;

use Amasty\MultiInventory\Helper\System;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

/**
 * Class Google
 */
class Google extends Template
{
    /**
     * @var System
     */
    private $helper;

    /**
     * Google constructor.
     *
     * @param Context $context
     * @param System $helper
     * @param array|null $data
     */
    public function __construct(
        Context $context,
        System $helper,
        array $data = null
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * @return bool
     */
    public function isAddressSuggestionEnabled()
    {
        return $this->helper->isAddressSuggestionEnabled();
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->helper->getGoogleMapsKey();
    }
}
