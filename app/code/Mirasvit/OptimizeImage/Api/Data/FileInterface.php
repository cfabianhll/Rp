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



namespace Mirasvit\OptimizeImage\Api\Data;

interface FileInterface
{
    const TABLE_NAME = 'mst_optimize_image_file';

    const ID             = 'file_id';
    const BASENAME       = 'basename';
    const RELATIVE_PATH  = 'relative_path';
    const FILE_EXTENSION = 'file_extension';
    const WEBP_PATH      = 'webp_path';
    const ORIGINAL_SIZE  = 'original_size';
    const ACTUAL_SIZE    = 'actual_size';
    const CREATED_AT     = 'created_at';
    const PROCESSED_AT   = 'processed_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getBasename();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBasename($value);

    /**
     * @return string
     */
    public function getRelativePath();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setRelativePath($value);

    /**
     * @return string
     */
    public function getFileExtension();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setFileExtension($value);

    /**
     * @return string
     */
    public function getWebpPath();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setWebpPath($value);

    /**
     * @return int
     */
    public function getOriginalSize();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setOriginalSize($value);

    /**
     * @return int
     */
    public function getActualSize();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setActualSize($value);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @return string
     */
    public function getProcessedAt();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setProcessedAt($value);
}
