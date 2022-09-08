<?php

namespace Trootech\Townshipcitymapping\Model\ResourceModel\Township;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Trootech\Townshipcitymapping\Model\Township', 'Trootech\Townshipcitymapping\Model\ResourceModel\Township');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
    public function toOptionArray()
    {
        $options = [];
        $propertyMap = [
            'value'         => 'township_id',
            'title'         => 'township_name',
            'country_name'    => 'country_name',
            'city_name'     => 'city_name'
        ];

        foreach ($this as $item) {
            $option = [];
            foreach ($propertyMap as $code => $field) {
                $option[$code] = $item->getData($field);
            }
            $option['label'] = $item->getName();
            $options[] = $option;
        }

        array_unshift(
            $options,
            ['title' => '', 'value' => '', 'label' => __('Please select a corregimiento.')]
        );

        return $options;
    }

}
?>
