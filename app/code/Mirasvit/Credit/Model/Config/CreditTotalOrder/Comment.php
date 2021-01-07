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



namespace Mirasvit\Credit\Model\Config\CreditTotalOrder;

use Magento\Config\Model\Config\CommentInterface;
use Magento\Sales\Model\Config as SalesConfig;

class Comment implements CommentInterface
{
    /**
     * @var SalesConfig
     */
    private $salesConfig;

    /**
     * Comment constructor.
     * @param SalesConfig $salesConfig
     */
    public function __construct(
        SalesConfig $salesConfig
    ) {
        $this->salesConfig = $salesConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommentText($elementValue)
    {
        $totals = $this->salesConfig->getGroupTotals('quote', 'totals');

        $sort = [];
        foreach ($totals as $key => $total) {
            $order      = isset($total['sort_order']) ? $total['sort_order'] : 0;
            $sort[$key] = $order;
        }
        asort($sort);

        $html = '<div class="mst-credit__config-totals"><table><tr><th>Total Name</th><th>Order</th></tr>';

        foreach ($sort as $key => $order) {
            $name = ucwords(str_replace('_', ' ', $key));
            $html .= '<tr><td class="' . $key . '">' . $name . '</td>
                <td class="' . $key . '">' . $order . '</td></tr>';
        }
        $html .= '</table></div>';

        return $html;
    }
}