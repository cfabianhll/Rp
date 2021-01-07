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



namespace Mirasvit\OptimizeJs\Controller\Bundle;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Asset\Minification;
use Mirasvit\OptimizeJs\Api\Repository\BundleFileRepositoryInterface;

class Track extends Action
{
    private $bundleFileRepository;

    private $minification;

    public function __construct(
        BundleFileRepositoryInterface $bundleFileRepository,
        Minification $minification,
        Context $context
    ) {
        $this->bundleFileRepository = $bundleFileRepository;
        $this->minification         = $minification;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $layout = $this->getRequest()->getParam('layout', '');
        $urls   = $this->getRequest()->getParam('urls', []);

        foreach ($urls as $url) {
            if (preg_match('/.*?(frontend|base)\/([^\/]+)\/([^\/]+)\/([^\/]+)\/(.*)$/i', $url, $matches)) {
                if (count($matches) !== 6) {
                    continue;
                }

                $area   = $matches[1];
                $theme  = $matches[2] . '/' . $matches[3];
                $locale = $matches[4];
                $file   = $this->minification->removeMinifiedSign($matches[5]);

                $bundleFile = $this->bundleFileRepository->create()
                    ->setArea($area)
                    ->setTheme($theme)
                    ->setLocale($locale)
                    ->setLayout($layout)
                    ->setFilename($file);

                $this->bundleFileRepository->ensure($bundleFile);
            }
        }

        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();
        $response->representJson(\Zend_Json_Encoder::encode([
            'success' => true,
        ]));
    }
}
