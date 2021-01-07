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


namespace Mirasvit\Rma\Service\Status;

use Mirasvit\Rma\Api\Config\RmaConfigInterface;
use Mirasvit\Rma\Api\Data\StatusInterface;
use Mirasvit\Rma\Api\Repository\StatusRepositoryInterface;
use Mirasvit\Rma\Api\Service\Status\StatusManagementInterface;

/**
 * We put here only methods directly connected with RMA properties
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StatusManagement implements StatusManagementInterface
{
    /**
     * @var RmaConfigInterface
     */
    private $rmaConfig;
    /**
     * @var StatusRepositoryInterface
     */
    private $statusRepository;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @param RmaConfigInterface $rmaConfig
     * @param StatusRepositoryInterface $statusRepository
     */
    public function __construct(
        RmaConfigInterface $rmaConfig,
        StatusRepositoryInterface $statusRepository
    ) {
        $this->rmaConfig        = $rmaConfig;
        $this->statusRepository = $statusRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function isStatusTreeUsed()
    {
        $collection = $this->statusRepository->getCollection()
            ->addActiveFilter()
            ->addFieldToFilter(StatusInterface::KEY_CHILDREN_IDS, ['neq' => ''])
        ;

        return $collection->count() > 0;
    }
}

