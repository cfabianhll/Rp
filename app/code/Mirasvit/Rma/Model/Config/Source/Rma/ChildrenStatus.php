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



namespace Mirasvit\Rma\Model\Config\Source\Rma;

use Magento\Framework\Registry;
use Mirasvit\Rma\Helper\Rma\Option;

class ChildrenStatus implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Option
     */
    private $optionHelper;
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var array
     */
    protected $options;

    /**
     * ChildrenStatus constructor.
     * @param Registry $registry
     * @param Option $optionHelper
     */
    public function __construct(
        Registry $registry,
        Option $optionHelper
    ) {
        $this->optionHelper = $optionHelper;
        $this->registry     = $registry;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $currentStatus = $this->registry->registry('current_status');
        if (!$this->options) {
            $this->options = [];
            $statuses = $this->optionHelper->getStatusList();
            foreach ($statuses as $status) {
                if ($currentStatus->getId() != $status->getId()) {
                    $this->options[] = ['value' => $status->getId(), 'label' => $status->getName()];
                }
            }
        }

        return $this->options;
    }
}
