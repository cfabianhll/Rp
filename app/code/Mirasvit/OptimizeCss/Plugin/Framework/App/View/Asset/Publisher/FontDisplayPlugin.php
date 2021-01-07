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



namespace Mirasvit\OptimizeCss\Plugin\Framework\App\View\Asset\Publisher;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * @see \Magento\Framework\App\View\Asset\Publisher
 */
class FontDisplayPlugin
{
    /** @var \Magento\Framework\View\Asset\LocalInterface */
    private $asset;

    private $filesystem;

    public function __construct(
        Filesystem $filesystem
    ) {
        $this->filesystem = $filesystem;
    }

    /**
     * @param \Magento\Framework\App\View\Asset\Publisher $subject
     * @param \Magento\Framework\View\Asset\LocalInterface $asset
     *
     * @return array
     */
    public function beforePublish($subject, $asset)
    {
        $this->asset = $asset;

        return [$asset];
    }

    /**
     * @param \Magento\Framework\App\View\Asset\Publisher $subject
     * @param bool $result
     *
     * @return bool
     */
    public function afterPublish($subject, $result)
    {
        if (pathinfo($this->asset->getFilePath(), PATHINFO_EXTENSION) != 'css') {
            return $result;
        }

        $targetDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

        $path = $targetDir->getAbsolutePath($this->asset->getPath());

        if (file_exists($path)) {
            $content = file_get_contents($path);

            $content = preg_replace_callback(
                '/@font-face\s*{[^}]+}/',
                [$this, 'replaceCallback'],
                $content
            );

            file_put_contents($path, $content);
        }

        return $result;
    }

    /**
     * @param array $match
     *
     * @return string
     */
    public function replaceCallback(array $match)
    {
        $fontFace = $match[0];
        $fontFace = str_replace('}', ';font-display:swap;}', $fontFace);

        return $fontFace;
    }
}
