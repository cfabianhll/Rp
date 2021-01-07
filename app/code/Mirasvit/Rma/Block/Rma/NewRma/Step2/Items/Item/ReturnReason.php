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


namespace Mirasvit\Rma\Block\Rma\NewRma\Step2\Items\Item;

class ReturnReason extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mirasvit\Rma\Api\Data\ItemInterface
     */
    protected $item;
    /**
     * @var \Mirasvit\Rma\Helper\Item\Option
     */
    private $rmaItemOption;
    /**
     * @var \Mirasvit\Rma\Service\Config\RmaRequirementConfig
     */
    private $config;
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;

    /**
     * ReturnReason constructor.
     * @param \Mirasvit\Rma\Helper\Item\Option $rmaItemOption
     * @param \Mirasvit\Rma\Service\Config\RmaRequirementConfig $config
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Rma\Helper\Item\Option $rmaItemOption,
        \Mirasvit\Rma\Service\Config\RmaRequirementConfig $config,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->rmaItemOption = $rmaItemOption;
        $this->config        = $config;
        $this->context       = $context;

        parent::__construct($context, $data);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return $this
     */
    public function setItem(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return (bool)$this->config->getGeneralCustomerRequirement();
    }

    /**
     * @return bool
     */
    public function isReasonAllowed()
    {
        return $this->config->isCustomerReasonRequired();
    }

    /**
     * @return bool
     */
    public function isConditionAllowed()
    {
        return $this->config->isCustomerConditionRequired();
    }

    /**
     * @return bool
     */
    public function isResolutionAllowed()
    {
        return $this->config->isCustomerResolutionRequired();
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ItemInterface
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ConditionInterface[]
     */
    public function getConditionList()
    {
        return $this->rmaItemOption->getConditionList();
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ResolutionInterface[]
     */
    public function getResolutionList()
    {
        return $this->rmaItemOption->getResolutionList();
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ReasonInterface[]
     */
    public function getReasonList()
    {
        return $this->rmaItemOption->getReasonList();
    }
}