<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface WarehouseQuoteItemInterface extends WarehouseAbstractInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ITEM_ID = 'item_id';

    const QUOTE_ID = 'quote_id';

    const QUOTE_ITEM_ID = 'quote_item_id';

    const QTY = 'qty';

    /**#@-*/

    /**
     * @return int
     */
    public function getQuoteId();

    /**
     * @return int
     */
    public function getQuoteItemId();

    /**
     * @return float
     */
    public function getQty();

    /**
     * @param int $id
     *
     * @return WarehouseQuoteItemInterface
     */
    public function setQuoteId($id);

    /**
     * @param int $id
     *
     * @return WarehouseQuoteItemInterface
     */
    public function setQuoteItemId($id);

    /**
     * @param float $qty
     *
     * @return WarehouseQuoteItemInterface
     */
    public function setQty($qty);
}
