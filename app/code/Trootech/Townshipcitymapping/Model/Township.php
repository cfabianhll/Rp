<?php
namespace Trootech\Townshipcitymapping\Model;

class Township extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Trootech\Townshipcitymapping\Model\ResourceModel\Township');
    }
}
?>