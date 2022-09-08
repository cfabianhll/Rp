<?php
namespace Trootech\Townshipcitymapping\Model\ResourceModel;

class Township extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('township_city_combination', 'township_id');
    }
}
?>