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



namespace Mirasvit\OptimizeImage\Service;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Shell;
use Mirasvit\OptimizeImage\Api\Data\FileInterface;
use Mirasvit\OptimizeImage\Model\Config;

class WebpService
{
    /**
     * @var Shell
     */
    private $shell;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Shell $shell,
        Config $config
    ) {
        $this->shell  = $shell;
        $this->config = $config;
    }

    /**
     * @param FileInterface $file
     *
     * @return FileInterface
     * @throws NotFoundException
     */
    public function process(FileInterface $file)
    {
        $absPath = $this->config->getAbsolutePath($file->getRelativePath());

        if (!file_exists($absPath)) {
            throw new NotFoundException(__('The file was removed: %1', $absPath));
        }

        switch ($file->getFileExtension()) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                $this->generateWebp($absPath);
                $file->setWebpPath($file->getRelativePath() . Config::WEBP_SUFFIX);

                break;
        }

        return $file;
    }

    /**
     * @param string $path
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function generateWebp($path)
    {
        $command = Config::CMD_PROCESS_WEBP;

        $newPath = $path . Config::WEBP_SUFFIX;

        if (file_exists($newPath)) {
            return;
        }

        try {
            $this->shell->execute(sprintf($command, $path, $newPath));
        } catch (\Exception $e) {
            if ($convertedPath = $this->normalize($path)) {
                $this->shell->execute(sprintf($command, $convertedPath, $newPath));
                unlink($convertedPath);
            }

            return;
        }
    }

    /**
     * Normilize image when error appears during webp convertion
     *
     * @param string $path
     *
     * @return bool|string
     */
    private function normalize($path)
    {
        $convertedPath = $path . Config::CONVERT_SUFFIX;

        try {
            $this->shell->execute(sprintf(Config::CMD_CONVERT_RGB, $path, $convertedPath));

            return $convertedPath;
        } catch (\Exception $e) {
            return false;
        }
    }
}
