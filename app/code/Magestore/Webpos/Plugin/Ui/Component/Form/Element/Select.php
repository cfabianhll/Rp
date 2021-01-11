<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Plugin\Ui\Component\Form\Element;

use Magestore\Webpos\Model\Service\Catalog\IsHideCustomSaleFlag;

/**
 * Class Select
 *
 * Plugin select option
 */
class Select
{
    /**
     * @var IsHideCustomSaleFlag
     */
    protected $isHideCustomSaleFlag;

    /**
     * Select constructor.
     *
     * @param IsHideCustomSaleFlag $isHideCustomSaleFlag
     */
    public function __construct(
        IsHideCustomSaleFlag $isHideCustomSaleFlag
    ) {
        $this->isHideCustomSaleFlag = $isHideCustomSaleFlag;
    }

    /**
     * Before prepare
     *
     * @param \Magento\Ui\Component\Form\Element\Select $select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePrepare(
        \Magento\Ui\Component\Form\Element\Select $select
    ) {
        $this->isHideCustomSaleFlag->setHideCustomSaleFlag(1);
    }
}
