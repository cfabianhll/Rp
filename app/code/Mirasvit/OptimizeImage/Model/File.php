<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-optimize
 * @version   1.0.5
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\OptimizeImage\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\OptimizeImage\Api\Data\FileInterface;

class File extends AbstractModel implements FileInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\File::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getBasename()
    {
        return $this->getData(self::BASENAME);
    }

    /**
     * {@inheritDoc}
     */
    public function setBasename($value)
    {
        return $this->setData(self::BASENAME, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getRelativePath()
    {
        return $this->getData(self::RELATIVE_PATH);
    }

    /**
     * {@inheritDoc}
     */
    public function setRelativePath($value)
    {
        return $this->setData(self::RELATIVE_PATH, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getFileExtension()
    {
        return $this->getData(self::FILE_EXTENSION);
    }

    /**
     * {@inheritDoc}
     */
    public function setFileExtension($value)
    {
        return $this->setData(self::FILE_EXTENSION, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getWebpPath()
    {
        return $this->getData(self::WEBP_PATH);
    }

    /**
     * {@inheritDoc}
     */
    public function setWebpPath($value)
    {
        return $this->setData(self::WEBP_PATH, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getOriginalSize()
    {
        return $this->getData(self::ORIGINAL_SIZE);
    }

    /**
     * {@inheritDoc}
     */
    public function setOriginalSize($value)
    {
        return $this->setData(self::ORIGINAL_SIZE, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getActualSize()
    {
        return $this->getData(self::ACTUAL_SIZE);
    }

    /**
     * {@inheritDoc}
     */
    public function setActualSize($value)
    {
        return $this->setData(self::ACTUAL_SIZE, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function getProcessedAt()
    {
        return $this->getData(self::PROCESSED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function setProcessedAt($value)
    {
        return $this->setData(self::PROCESSED_AT, $value);
    }
}
