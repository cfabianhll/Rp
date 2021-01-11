<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\OrderSuccess\Ui\DataProvider;

/**
 * Class AwaitingPaymentDataProvider
 * @package Magestore\OrderSuccess\Ui\DataProvider
 */
class AwaitingPaymentDataProvider extends \Magestore\OrderSuccess\Ui\DataProvider\OrderDataProvider
{
    /**
     * {@inheritdoc}
     */
    public function getOrderCollection()
    {
        /** @var Collection $collection */
        $collection= $this->context->getAwaitingPaymentCollectionFactory()->create();
        return $collection;
    }
    
}
