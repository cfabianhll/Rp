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
 * @package   mirasvit/module-credit
 * @version   1.0.79
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Credit\Plugin\Sales\Model\Config;

use Magento\Sales\Model\Config as SalesConfig;

class ChangeCreditTotalOrderPlugin
{
    /**
     * @var \Mirasvit\Credit\Service\Config\CalculationConfig
     */
    private $calculationConfig;

    /**
     * ChangeCreditTotalOrderPlugin constructor.
     * @param \Mirasvit\Credit\Service\Config\CalculationConfig $calculationConfig
     */
    public function __construct(
        \Mirasvit\Credit\Service\Config\CalculationConfig $calculationConfig
    ) {
        $this->calculationConfig = $calculationConfig;
    }

    /**
     * @param SalesConfig $config
     * @param \callable   $proceed
     * @param string      $section
     * @param string      $group
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetGroupTotals(SalesConfig $config, $proceed, $section, $group)
    {
        $result = $proceed($section, $group);

        if ($group != 'totals' || !isset($result['credit'])) {
            return $result;
        }

        $result['credit']['sort_order'] = $this->calculationConfig->getCreditTotalOrder();

        return $result;
    }
}