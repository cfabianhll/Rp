<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Model\Service\Catalog;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;

/**
 * Class IsHideCustomSaleFlag
 *
 * Hide Custom sale flag
 */
class IsHideCustomSaleFlag extends DataObject
{
    const HIDE_CUSTOM_SALE_FLAG = 'hide_custom_sale_flag';

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * IsHideCustomSaleFlag constructor.
     *
     * @param RequestInterface $request
     * @param array $data
     */
    public function __construct(
        RequestInterface  $request,
        array $data = []
    ) {
        $this->request = $request;
        parent::__construct($data);
    }

    /**
     * Set hide custom sale flag
     *
     * @param int $flag
     * @return $this;
     */
    public function setHideCustomSaleFlag(int $flag)
    {
        return $this->setData(self::HIDE_CUSTOM_SALE_FLAG, $flag);
    }

    /**
     * Get hide custom sale flag
     *
     * @return int
     */
    public function getHideCustomSaleFlag()
    {
        if ($this->request->getFullActionName() === 'catalog_product_index') {
            return 1;
        }
        return $this->_getData(self::HIDE_CUSTOM_SALE_FLAG);
    }
}
