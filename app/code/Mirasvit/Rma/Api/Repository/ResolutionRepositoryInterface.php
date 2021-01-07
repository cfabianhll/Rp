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


interface ResolutionRepositoryInterface
{
    /**
     * @param \Mirasvit\Rma\Api\Data\ResolutionInterface $resolution
     * @return \Mirasvit\Rma\Api\Data\ResolutionInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Mirasvit\Rma\Api\Data\ResolutionInterface $resolution);

    /**
     * @param int $resolutionId
     * @return \Mirasvit\Rma\Api\Data\ResolutionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($resolutionId);

    /**
     * @param string $code
     * @return \Mirasvit\Rma\Api\Data\ResolutionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByCode($code);

    /**
     * @param \Mirasvit\Rma\Api\Data\ResolutionInterface $resolution resolution which will deleted
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(\Mirasvit\Rma\Api\Data\ResolutionInterface $resolution);

    /**
     * @param int $resolutionId
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($resolutionId);
}