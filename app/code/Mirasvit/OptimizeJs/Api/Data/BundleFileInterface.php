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



namespace Mirasvit\OptimizeJs\Api\Data;

interface BundleFileInterface
{
    const TABLE_NAME = 'mst_optimize_js_bundle_file';

    const ID = 'file_id';

    const AREA     = 'area';
    const LAYOUT   = 'layout';
    const THEME    = 'theme';
    const LOCALE   = 'locale';
    const FILENAME = 'filename';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getArea();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setArea($value);

    /**
     * @return string
     */
    public function getLayout();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setLayout($value);

    /**
     * @return string
     */
    public function getTheme();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setTheme($value);

    /**
     * @return string
     */
    public function getLocale();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setLocale($value);

    /**
     * @return string
     */
    public function getFilename();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setFilename($value);
}
