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



namespace Mirasvit\Rma\Block\Adminhtml\Status;

use Magento\Backend\Block\Template\Context;
use Mirasvit\Rma\Helper\StatusTree;

class Diagram extends \Magento\Backend\Block\Template
{
    /**
     * @var StatusTree
     */
    private $statusTreeHelper;

    /**
     * Diagram constructor.
     * @param StatusTree $statusTreeHelper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        StatusTree $statusTreeHelper,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->statusTreeHelper = $statusTreeHelper;
    }

    /**
     * @return array
     */
    public function getTree()
    {
        return $this->statusTreeHelper->getDiagramTree();
    }
}