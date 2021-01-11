<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Ui\DataProvider\Archive\Form;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magestore\Stocktaking\Api\Data\StocktakingArchiveItemInterface;

/**
 * Class Product
 *
 * Used for data Product List
 */
class Product extends DataProvider
{
    /**
     * Prepare update url
     *
     * @return void
     */
    protected function prepareUpdateUrl()
    {
        $this->data['config']['filter_url_params'] = [
            StocktakingArchiveItemInterface::STOCKTAKING_ID => $this->request->getParam('stocktaking_id')
        ];
        parent::prepareUpdateUrl();
    }
}
