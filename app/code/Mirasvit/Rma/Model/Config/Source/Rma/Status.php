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

class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Mirasvit\Rma\Helper\Rma\Option
     */
    private $optionHelper;

    /**
     * Status constructor.
     * @param \Mirasvit\Rma\Helper\Rma\Option $optionHelper
     */
    public function __construct(
        \Mirasvit\Rma\Helper\Rma\Option $optionHelper
    ) {
        $this->optionHelper = $optionHelper;
    }

    /**
     * @var array
     */
    protected $options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [];
            $statuses = $this->optionHelper->getStatusList();
            foreach ($statuses as $status) {
                $this->options[] = ['value' => $status->getId(), 'label' => $status->getName()];
            }
        }

        return $this->options;
    }
}
