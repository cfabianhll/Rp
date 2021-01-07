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



namespace Mirasvit\OptimizeJs\Plugin\RequireJs\Model\FileManager;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\RequireJs\Config as RequireJsConfig;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\RequireJs\Model\FileManager;
use Magento\Framework\View\Asset\File;
use Mirasvit\OptimizeJs\Model\Config;

/**
 * @see \Magento\RequireJs\Model\FileManager
 */
class FilterBundleFilesPlugin
{
    /** @var Config  */
    private $config;

    /** @var RequestInterface */
    private $request;

    /** @var Filesystem  */
    private $filesystem;

    /** @var Minification  */
    private $minification;

    /** @var AssetRepository  */
    private $assetRepo;

    public function __construct(
        Config $config,
        RequestInterface $request,
        Filesystem $filesystem,
        AssetRepository $assetRepo,
        Minification $minification
    ) {
        $this->config       = $config;
        $this->request      = $request;
        $this->filesystem   = $filesystem;
        $this->assetRepo    = $assetRepo;
        $this->minification = $minification;
    }

    /**
     * @param FileManager $subject
     * @param File[]            $bundles
     *
     * @return File[]
     */
    public function afterCreateBundleJsPool(FileManager $subject, $bundles)
    {
        if (!$this->config->isEnabled()) {
            return $bundles;
        }

        $bundles = $this->getAllBundles();

        $result = [];

        $layout = $this->request->getFullActionName();
        /** @var File $bundle */
        foreach ($bundles as $bundle) {
            $filepath = $this->minification->removeMinifiedSign($bundle->getFilePath());
            $filename = pathinfo($filepath, PATHINFO_FILENAME);

            if (strpos($filename, '_') === false) {
                $result[] = $bundle;
                continue;
            }

            if ($filename == 'bundle_default' || $filename == 'bundle_' . $layout) {
                $result[] = $bundle;
            }
        }

        return $result;
    }

    /**
     * @param FileManager $subject
     * @param File|false            $result
     *
     * @return File
     */
    public function afterCreateStaticJsAsset(FileManager $subject, $result)
    {
        if (!$this->config->isEnabled()) {
            return $result;
        }

        return $this->assetRepo->createAsset(RequireJsConfig::STATIC_FILE_NAME);
    }

    private function getAllBundles()
    {
        $libDir  = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
        $context = $this->assetRepo->getStaticViewFileContext();

        $bundleDir = $context->getPath() . '/' . RequireJsConfig::BUNDLE_JS_DIR;

        if (!$libDir->isExist($bundleDir)) {
            return [];
        }

        foreach ($libDir->read($bundleDir) as $bundleFile) {
            if (pathinfo($bundleFile, PATHINFO_EXTENSION) !== 'js') {
                continue;
            }
            $relPath   = $libDir->getRelativePath($bundleFile);
            $bundles[] = $this->assetRepo->createArbitrary($relPath, '');
        }

        return $bundles;
    }
}
