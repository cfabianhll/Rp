<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api;

use Amasty\MultiInventory\Api\Data\ExportInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface ExportRepositoryInterface
{
    /**
     * Save Export.
     *
     * @param ExportInterface $item
     *
     * @return ExportInterface
     * @throws CouldNotSaveException
     */
    public function save(ExportInterface $item);

    /**
     * Retrieve store.
     *
     * @param int $id
     *
     * @return ExportInterface
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete Export.
     *
     * @param ExportInterface $item
     *
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(ExportInterface $item);

    /**
     * Delete Export by ID.
     *
     * @param int $id
     *
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById($id);
}
