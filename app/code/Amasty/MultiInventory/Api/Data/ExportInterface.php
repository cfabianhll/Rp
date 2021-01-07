<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface ExportInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const EXPORT_ID = 'export_id';

    const FILE = 'file';

    const CREATE_TIME = 'create_time';

    const PATH_EXPORT = 'amasty_export_stock/';

    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getFile();

    /**
     * @return string
     */
    public function getCreateTime();

    /**
     * @param int $id
     *
     * @return ExportInterface
     */
    public function setId($id);

    /**
     * @param string $file
     *
     * @return ExportInterface
     */
    public function setFile($file);

    /**
     * @param string $time
     *
     * @return ExportInterface
     */
    public function setCreateTime($time);
}
