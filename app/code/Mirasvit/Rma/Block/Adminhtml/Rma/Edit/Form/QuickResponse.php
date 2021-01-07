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


namespace Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form;


class QuickResponse extends \Magento\Backend\Block\Template
{
    /**
     * @var \Mirasvit\Rma\Helper\Message\Option
     */
    private $quickResponseOption;

    /**
     * QuickResponse constructor.
     * @param \Mirasvit\Rma\Helper\Message\Option $quickResponseOption
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Rma\Helper\Message\Option $quickResponseOption,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->quickResponseOption = $quickResponseOption;

        parent::__construct($context, $data);
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\QuickResponseInterface[]
     */
    public function getQuickResponse()
    {
        return $this->quickResponseOption->getOptionsList();
    }
}