<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model;

use Amasty\MultiInventory\Api\Data\ExportInterface;
use Amasty\MultiInventory\Model\ResourceModel\Export as ExportResource;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class Export
 */
class Export extends AbstractExtensibleModel implements ExportInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ExportResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::EXPORT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getFile()
    {
        return $this->getData(self::FILE);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreateTime()
    {
        return $this->getData(self::CREATE_TIME);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::EXPORT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setFile($file)
    {
        $this->setData(self::FILE, $file);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreateTime($time)
    {
        $this->setData(self::CREATE_TIME, $time);

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return ['sku', 'code', 'qty'];
    }
}
