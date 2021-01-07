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
 * @package   mirasvit/module-rma
 * @version   2.1.12
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Api\Repository;


interface AttachmentRepositoryInterface
{
    /**
     * @return \Mirasvit\Rma\Api\Data\AttachmentInterface
     */
    public function create();

    /**
     * @param \Mirasvit\Rma\Api\Data\AttachmentInterface $attachment
     * @return \Mirasvit\Rma\Api\Data\AttachmentInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Mirasvit\Rma\Api\Data\AttachmentInterface $attachment);

    /**
     * @param int $attachmentId
     * @return \Mirasvit\Rma\Api\Data\AttachmentInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($attachmentId);


    /**
     * @param int $attachmentId
     * @return \Mirasvit\Rma\Api\Data\AttachmentInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByUid($attachmentId);

    /**
     * @param \Mirasvit\Rma\Api\Data\AttachmentInterface $attachment rma which will deleted
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(\Mirasvit\Rma\Api\Data\AttachmentInterface $attachment);

    /**
     * @param int $attachmentId
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($attachmentId);
}