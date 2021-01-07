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



namespace Mirasvit\OptimizeImage\Processor;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Optimize\Api\Processor\OutputProcessorInterface;
use Mirasvit\OptimizeImage\Model\Config;

class WebpProcessor implements OutputProcessorInterface
{
    private $config;

    private $mediaUrl;

    private $mediaDir;

    public function __construct(
        Config $config,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->config   = $config;
        $this->mediaUrl = $storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $this->mediaDir = $filesystem->getDirectoryread(DirectoryList::MEDIA);
    }

    /**
     * {@inheritdoc}
     */
    public function process($content)
    {
        if (!$this->config->isWebpEnabled()) {
            return $content;
        }

        $content = preg_replace_callback(
            '/(<\s*img[^>]+)src\s*=\s*["\']([^"\']+)[\'"]([^>]{0,}>)/is',
            [$this, 'replaceCallback'],
            $content
        );

        return $content;
    }

    /**
     * @param array $match
     *
     * @return string
     */
    private function replaceCallback(array $match)
    {
        if ($this->config->isWebpException($match[0])) {
            return $match[0];
        }

        $url = $match[2];
        if (strpos($url, $this->mediaUrl) === false) {
            return $match[0];
        }

        $path = str_replace($this->mediaUrl, '', $url);

        $webpPath = $path . Config::WEBP_SUFFIX;

        if (!$this->mediaDir->isExist($webpPath)) {
            return $match[0];
        }

        $webpUrl = str_replace($path, $webpPath, $match[2]);

        return '<picture><source srcset="' . $webpUrl . '" type="image/webp"/>' . $match[0] . '</picture>';
    }
}
