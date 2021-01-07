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


namespace Mirasvit\Rma\Service\Message\MessageManagement;

use \Mirasvit\Rma\Api\Data\MessageInterface;

/**
 *  We put here only methods directly connected with Message properties
 */
class Search implements \Mirasvit\Rma\Api\Service\Message\MessageManagement\SearchInterface
{
    /**
     * @var \Mirasvit\Rma\Api\Repository\MessageRepositoryInterface
     */
    private $messageRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Search constructor.
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Mirasvit\Rma\Api\Repository\MessageRepositoryInterface $messageRepository
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Mirasvit\Rma\Api\Repository\MessageRepositoryInterface $messageRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->messageRepository     = $messageRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibleInFront(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rma->getId())
            ->addFilter('is_visible_in_frontend', true)
        ;
        return $this->messageRepository->getList($searchCriteria->create())->getItems();
    }
}