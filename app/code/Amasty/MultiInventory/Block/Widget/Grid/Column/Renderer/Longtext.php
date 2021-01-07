<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;

/**
 * Class Longtext
 */
class Longtext extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Longtext
{
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * Longtext constructor.
     * @param Context $context
     * @param UrlInterface $url
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlInterface $url,
        array $data = []
    ) {
        $this->url = $url;
        parent::__construct($context, $data);
    }

    /**
     * Add url for Warehouse
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $code = $row->getData('code');
        $url = $this->url->getUrl(
            'amasty_multi_inventory/warehouse/edit',
            ['warehouse_id' => $row->getData('warehouse_id')]
        );
        $text = parent::render($row);
        // @codingStandardsIgnoreLine
        $text .= sprintf(' (<a href="%s">%s</a>)', $url, $code);

        return $text;
    }
}
