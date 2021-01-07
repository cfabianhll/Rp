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



namespace Mirasvit\Rma\Block\Rma\NewRma\Step2;

class Policy extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    private $catalogHelper;
    /**
     * @var \Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface
     */
    private $policyConfig;
    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    private $blockFactory;
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;

    /**
     * Policy constructor.
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param \Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface $policyConfig
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface $policyConfig,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->catalogHelper = $catalogHelper;
        $this->policyConfig  = $policyConfig;
        $this->blockFactory  = $blockFactory;
        $this->context       = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return bool|int
     */
    public function getPolicyIsActive()
    {
        return $this->policyConfig->getIsActive();
    }

    /**
     * @var object
     */
    protected $pblock;

    /**
     * @return object
     */
    public function getPolicyBlock()
    {
        if (!$this->pblock) {
            $this->pblock = $this->blockFactory->create()->load($this->policyConfig->getPolicyBlock());
        }

        return $this->pblock;
    }

    /**
     * @return string
     */
    public function getPolicyTitle()
    {
        return $this->getPolicyBlock()->getTitle();
    }

    /**
     * @return string
     */
    public function getPolicyContent()
    {
        $processor = $this->catalogHelper->getPageTemplateProcessor();

        return $processor->filter($this->getPolicyBlock()->getContent());
    }
}