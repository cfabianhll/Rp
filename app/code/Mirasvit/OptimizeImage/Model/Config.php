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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Config
{
    const CMD_CONVERT_RGB  = 'convert -colorspace RGB %s %s';
    const CMD_PROCESS_WEBP = 'cwebp -q 90 \'%s\' -o \'%s\'';
    const CMD_PROCESS_PNG  = 'optipng \'%s\'';
    const CMD_PROCESS_GIF  = 'gifsicle \'%s\' -o \'%s\'';
    const CMD_PROCESS_JPG  = 'jpegoptim --all-progressive --strip-xmp --strip-com --strip-exif --strip-iptc \'%s\'';

    const WEBP_SUFFIX = '.mst.webp';
    const CONVERT_SUFFIX = '.mst.conv';

    private $fs;

    private $scopeConfig;

    public function __construct(
        Filesystem $fs,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->fs          = $fs;
        $this->scopeConfig = $scopeConfig;
    }

    public function isWebpEnabled()
    {
        return (bool)$this->scopeConfig->getValue('mst_optimize/optimize_image/is_webp');
    }

    /**
     * @param string $extension
     *
     * @return bool
     */
    public function isAllowedFileExtension($extension)
    {
        return in_array($extension, ['png', 'gif', 'jpg', 'jpeg']);
    }

    public function isLazyEnabled()
    {
        return $this->scopeConfig->getValue('mst_optimize/optimize_image/image_lazy_load/enabled');
    }

    /**
     * @param string $relativePath
     *
     * @return string
     */
    public function getAbsolutePath($relativePath)
    {
        $abs = $this->fs->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();

        return $abs . $relativePath;
    }

    /**
     * @param string $absolutePath
     *
     * @return string
     */
    public function getRelativePath($absolutePath)
    {
        $abs = $this->fs->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();

        return str_replace($abs, '', $absolutePath);
    }

    /**
     * @param string $img
     *
     * @return bool
     */
    public function isWebpException($img)
    {
        if (strpos($img, 'lazyOwl') !== false) {
            return true;
        }

        return false;
    }
}
