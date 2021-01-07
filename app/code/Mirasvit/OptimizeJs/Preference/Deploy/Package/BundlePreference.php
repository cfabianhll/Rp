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



namespace Mirasvit\OptimizeJs\Preference\Deploy\Package;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\WriteInterface;
use Magento\Framework\View\Asset\Minification;
use Mirasvit\OptimizeJs\Service\BundleFileService;

class BundlePreference implements BundleParentInterface
{
    /** @var Filesystem */
    private $filesystem;

    /** @var Minification */
    private $minification;

    /** @var Filesystem\Directory\WriteInterface */
    private $staticDir;

    /** @var string */
    private $area;

    /** @var string */
    private $theme;

    /** @var string */
    private $locale;

    /** @var string */
    private $pathToBundleDir;

    /** @var array */
    private $fileContent = [];

    /** @var array */
    private $files = [];

    /** @var BundleFileService */
    private $bundleFileService;

    private $scopes = null;

    /**
     * BundlePreference constructor.
     *
     * @param BundleFileService $bundleFileService
     * @param Filesystem        $filesystem
     * @param Minification      $minification
     * @param string            $area
     * @param string            $theme
     * @param string            $locale
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        BundleFileService $bundleFileService,
        Filesystem $filesystem,
        Minification $minification,
        $area,
        $theme,
        $locale
    ) {
        $this->bundleFileService = $bundleFileService;
        $this->filesystem        = $filesystem;
        $this->minification      = $minification;
        $this->staticDir         = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $this->area              = $area;
        $this->theme             = $theme;
        $this->locale            = $locale;
        $this->pathToBundleDir   = $this->area . '/' . $this->theme . '/' . $this->locale . '/' . self::BUNDLE_JS_DIR;
    }

    /**
     * {@inheritdoc}
     */
    public function addFile($filePath, $sourcePath, $contentType)
    {
        $poolName = $contentType === 'js' ? 'jsbuild' : 'text';

        $scopeNames = $contentType == 'js' ? $this->getScopeNames($filePath) : ['default'];

        foreach ($scopeNames as $scopeName) {
            $this->files[$scopeName][$poolName][$filePath] = $sourcePath;
        }
    }

    /**
     * @param string $filePath
     *
     * @return array
     */
    private function getScopeNames($filePath)
    {
        $result = [];

        if (!$this->scopes) {
            $this->scopes = $this->bundleFileService->getScopes($this->area, $this->theme, $this->locale);
        }

        $filePath = $this->minification->removeMinifiedSign($filePath);

        foreach ($this->scopes as $scopeName => $files) {
            if (in_array($filePath, $files)) {
                $result[] = $scopeName;
            }
        }

        return $result;
    }

    public function flush()
    {
        $bundleFile = null;

        foreach ($this->files as $scopeName => $pools) {
            try {
                $bundleFile = $this->startNewBundleFile($scopeName);

                foreach ($pools as $poolName => $files) {
                    if (empty($files)) {
                        continue;
                    }
                    $content = [];

                    foreach ($files as $filePath => $sourcePath) {
                        $content[$this->minification->addMinifiedSign($filePath)] = $this->getFileContent($sourcePath);
                    }

                    $this->addToBundleFile($bundleFile, $poolName, $content);
                }

                $this->endBundleFile($bundleFile);
                $bundleFile->write($this->getInitJs());
            } catch (\Exception $e) {
            }
        }

        $this->files = [];
    }

    public function clear()
    {
        $this->staticDir->delete($this->pathToBundleDir);
    }

    /**
     * @param string $scopeName
     *
     * @return WriteInterface
     */
    private function startNewBundleFile($scopeName)
    {
        $bundleFile = $this->staticDir->openFile(
            $this->minification->addMinifiedSign($this->pathToBundleDir . '/bundle_' . $scopeName . '.js')
        );

        return $bundleFile;
    }

    /**
     * @param WriteInterface $bundleFile
     * @param string         $poolName
     * @param array          $contents
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function addToBundleFile(WriteInterface $bundleFile, $poolName, array $contents)
    {
        $bundleFile->write("require.config({\"config\": {\n");
        $bundleFile->write("        \"{$poolName}\":");

        if ($contents) {
            $content = \Zend_Json::encode($contents);
            $bundleFile->write("{$content}\n");
        } else {
            $bundleFile->write("{}\n");
        }
        $bundleFile->write("}});\n");
    }

    /**
     * @param WriteInterface $bundleFile
     *
     * @return bool
     */
    private function endBundleFile(WriteInterface $bundleFile)
    {
        return true;
    }

    /**
     * @param string $sourcePath
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getFileContent($sourcePath)
    {
        if (!isset($this->fileContent[$sourcePath])) {
            $content = $this->staticDir->readFile($this->minification->addMinifiedSign($sourcePath));
            if (mb_detect_encoding($content) !== "UTF-8") {
                $content = mb_convert_encoding($content, "UTF-8");
            }

            $this->fileContent[$sourcePath] = $content;
        }

        return $this->fileContent[$sourcePath];
    }

    private function getInitJs()
    {
        return "require.config({\n" .
            "    bundles: {\n" .
            "        'mage/requirejs/static': [\n" .
            "            'jsbuild',\n" .
            "            'buildTools',\n" .
            "            'text',\n" .
            "            'statistician'\n" .
            "        ]\n" .
            "    },\n" .
            "    deps: [\n" .
            "        'jsbuild'\n" .
            "    ]\n" .
            "});\n";
    }
}
