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



namespace Mirasvit\ImageLazyLoad\Processor;

use Mirasvit\ImageLazyLoad\Model\Config;
use Mirasvit\ImageLazyLoad\Service\ImageService;
use Mirasvit\Optimize\Api\Processor\OutputProcessorInterface;

class LazyLoadProcessor implements OutputProcessorInterface
{
    private $config;

    private $imageService;

    private $imgCounter  = 0;

    private $webpCounter = 0;

    public function __construct(
        ImageService $imageService,
        Config $config
    ) {
        $this->config       = $config;
        $this->imageService = $imageService;
    }

    /**
     * {@inheritdoc}
     */
    public function process($content)
    {
        if (!$this->config->isEnabled()) {
            return $content;
        }

        $content = preg_replace_callback(
            '/(<\s*img[^>]+)(src\s*=\s*"[^"]+")([^>]{0,}>)/is',
            [$this, 'replaceImgCallback'],
            $content
        );

        $content = preg_replace_callback(
            '/(<\s*source[^>]+)(srcset\s*=\s*"[^"]+")([^>]{0,}>)/is',
            [$this, 'replaceSourceCallback'],
            $content
        );

        if ($this->imgCounter > 0) {
            $content = $this->appendLazyLoadLib($content);
        }

        return $content;
    }

    /**
     * @param array $match
     *
     * @return string
     */
    private function replaceImgCallback(array $match)
    {
        if (++$this->imgCounter <= $this->config->getSkipNumber()) {
            return $match[0];
        }

        if ($this->config->isException($match[0])) {
            return $match[0];
        }

        $url = substr($match[2], 5, strlen($match[2]) - 6);

        $px = $this->imageService->getPlaceholder($url, $this->config->isDebug());

        $replaced = $match[1] . 'src="' . $px . '" data-mst-lazy-src="' . $url . '"' . $match[3];
        $replaced = preg_replace('/class\s*=\s*"/i', 'class="mst-lazy ', $replaced);

        if (strpos($replaced, 'class=') === false) {
            $replaced = str_replace('>', 'class="mst-lazy">', $replaced);
        }

        $replaced .= '<noscript>' . $match[0] . '</noscript>';

        return $replaced;
    }

    /**
     * @param array $match
     *
     * @return string
     */
    private function replaceSourceCallback(array $match)
    {
        if (++$this->webpCounter <= $this->config->getSkipNumber()) {
            return $match[0];
        }

        if ($this->config->isException($match[0])) {
            return $match[0];
        }

        $url = substr($match[2], 8, strlen($match[2]) - 9);

        $px = $this->imageService->getPlaceholder($url, $this->config->isDebug());

        $replaced = $match[1] . 'srcset="' . $px . '" data-mst-lazy-srcset="' . $url . '"' . $match[3];
        $replaced .= '<noscript>' . $match[0] . '</noscript>';

        return $replaced;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    private function appendLazyLoadLib($content)
    {
        if ($this->config->isDebug()) {
            return $content;
        }

        $script = '
            <script>
                 window.lazySizesConfig = window.lazySizesConfig || {};
                 window.lazySizesConfig.lazyClass = "mst-lazy";
                 lazySizesConfig.srcAttr = "data-mst-lazy-src";
                 lazySizesConfig.srcsetAttr = "data-mst-lazy-srcset";
                 lazySizesConfig.loadMode = 1;

                 require(["Mirasvit_ImageLazyLoad/js/lazysizes"], function() {});
            </script>';

        return $content . $script;
    }
}
