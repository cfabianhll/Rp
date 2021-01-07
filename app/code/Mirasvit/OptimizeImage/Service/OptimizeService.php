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

class OptimizeService
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
    public function optimize(FileInterface $file)
    {
        $absPath = $this->config->getAbsolutePath($file->getRelativePath());

        if (!file_exists($absPath)) {
            throw new NotFoundException(__('The file was removed: %1', $absPath));
        }

        switch ($file->getFileExtension()) {
            case 'jpg':
            case 'jpeg':
                $this->processJpg($absPath);
                break;
            case 'png':
                $this->processPng($absPath);
                break;
            case 'gif':
                $this->processGif($absPath);
                break;
        }

        $file->setActualSize(filesize($absPath));
        $file->setProcessedAt(date('Y-m-d H:i:s'));

        return $file;
    }

    /**
     * @param string $path
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processJpg($path)
    {
        $command = Config::CMD_PROCESS_JPG;

        $this->shell->execute(sprintf($command, $path));
    }

    /**
     * @param string $path
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processPng($path)
    {
        $command = Config::CMD_PROCESS_PNG;

        $this->shell->execute(sprintf($command, $path));
    }

    /**
     * @param string $path
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processGif($path)
    {
        $command = Config::CMD_PROCESS_GIF;

        $this->shell->execute(sprintf($command, $path, $path));
    }
}
