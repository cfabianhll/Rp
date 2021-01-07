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



namespace Mirasvit\ImageLazyLoad\Service;

use Magento\Framework\App\Cache;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class ImageService
{
    private $mediaUrl;

    private $mediaDir;

    private $cache;

    public function __construct(
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        Cache $cache
    ) {
        $this->mediaUrl = $storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $this->mediaDir = $filesystem->getDirectoryread(DirectoryList::MEDIA);
        $this->cache    = $cache;
    }

    /**
     * @param string $url
     * @param bool   $isDebug
     *
     * @return string
     */
    public function getPlaceholder($url, $isDebug)
    {
        $px = $isDebug
            ? 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M/wHwAEBgIApD5fRAAAAABJRU5ErkJggg=='
            : 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        $path = $this->retrieveImageAbsPath($url);
        if (!$path) {
            return $px;
        }

        $size = $this->getImageSize($path);
        if (!$size) {
            return $px;
        }

        $cacheKey = __METHOD__ . $size[0] . $size[1] . $isDebug;

        $placeholder = $this->cache->load($cacheKey);

        if (!$placeholder) {
            try {
                $image = imagecreatetruecolor($size[0], $size[1]);
                $color = imagecolorallocate($image, 0, 255, 255);

                if (!$isDebug) {
                    imagecolortransparent($image, $color);
                }

                imagefilledrectangle($image, 0, 0, $size[0], $size[1], $color);

                ob_start();
                imagepng($image);
                $blob = ob_get_contents();
                ob_end_clean();
                imagedestroy($image);

                $placeholder = 'data:image/png;base64,' . base64_encode($blob);
            } catch (\Exception $e) {
                $placeholder = $px;
            }

            $this->cache->save($placeholder, $cacheKey);
        }

        return $placeholder;
    }

    /**
     * @param string $url
     *
     * @return string|false
     */
    private function retrieveImageAbsPath($url)
    {
        if (strpos($url, $this->mediaUrl) === false) {
            return false;
        }

        $path = str_replace($this->mediaUrl, '', $url);

        if (!$this->mediaDir->isExist($path)) {
            return false;
        }

        return $this->mediaDir->getAbsolutePath($path);
    }

    /**
     * @param string $absPath
     *
     * @return array|false
     */
    private function getImageSize($absPath)
    {
        try {
            $size = getimagesize($absPath);

            if (!$size) {
                return false;
            }

            return [$size[0], $size[1]];
        } catch (\Exception $e) {
            return false;
        }
    }
}
