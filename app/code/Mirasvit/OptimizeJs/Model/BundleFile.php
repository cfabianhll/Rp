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



namespace Mirasvit\OptimizeJs\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\OptimizeJs\Api\Data\BundleFileInterface;

class BundleFile extends AbstractModel implements BundleFileInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\BundleFile::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getArea()
    {
        return $this->getData(self::AREA);
    }

    /**
     * {@inheritdoc}
     */
    public function setArea($value)
    {
        return $this->setData(self::AREA, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getLayout()
    {
        return $this->getData(self::LAYOUT);
    }

    /**
     * {@inheritdoc}
     */
    public function setLayout($value)
    {
        return $this->setData(self::LAYOUT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getTheme()
    {
        return $this->getData(self::THEME);
    }

    /**
     * {@inheritdoc}
     */
    public function setTheme($value)
    {
        return $this->setData(self::THEME, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->getData(self::LOCALE);
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($value)
    {
        return $this->setData(self::LOCALE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilename()
    {
        return $this->getData(self::FILENAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setFilename($value)
    {
        return $this->setData(self::FILENAME, $value);
    }
}
