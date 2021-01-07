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



namespace Mirasvit\Rma\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Mirasvit\Rma\Api\Config\RmaConfigInterface;
use Mirasvit\Rma\Api\Data\RmaInterface;
use Mirasvit\Rma\Api\Data\StatusInterface;
use Mirasvit\Rma\Api\Repository\StatusRepositoryInterface;
use Mirasvit\Rma\Model\ResourceModel\Status\Collection;

class StatusTree extends AbstractHelper
{
    /**
     * @var int
     */
    private $currentStatusId;
    /**
     * @var array
     */
    private $statusTree    = [];
    /**
     * @var array
     */
    private $tmpStatusTree = [];

    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Status\Collection
     */
    private $statusDiagramCollection;

    /**
     * @var RmaConfigInterface
     */
    private $rmaConfig;
    /**
     * @var StatusRepositoryInterface
     */
    private $statusRepository;
    /**
     * @var Context
     */
    private $context;

    /**
     * StatusTree constructor.
     * @param RmaConfigInterface $rmaConfig
     * @param StatusRepositoryInterface $statusRepository
     * @param Context $context
     */
    public function __construct(
        RmaConfigInterface $rmaConfig,
        StatusRepositoryInterface $statusRepository,
        Context $context
    ) {
        $this->rmaConfig        = $rmaConfig;
        $this->statusRepository = $statusRepository;
        $this->context          = $context;

        parent::__construct($context);
    }

    /**
     * @param RmaInterface $rma
     * @return array
     */
    public function getRmaBranch($rma)
    {
        $this->currentStatusId = $rma->getStatusId();
        $statuses = $this->statusRepository->getCollection();
        $statuses->addActiveFilter();
        $statuses->getSelect()->order(StatusInterface::KEY_SORT_ORDER . ' asc');

        $rmaStatusHistory = (array)explode(',', $rma->getStatusHistory());

        $parents = [];
        $statusesTree = [];
        foreach ($statuses as $status) {
            // we need this query to sort children
            $childrenIds = [];
            foreach ($status->getChildrenIds() as $childrenId) {
                if (in_array($childrenId, $rmaStatusHistory) && !isset($parents[$childrenId])) {
                    $childrenIds[] = $childrenId;
                }
            }
            if (empty($childrenIds)) { // after upgrade RMA does not have history, so we use default
                $childrenIds = $status->getChildrenIds();
            }
            $children = $this->getChildrenStatuses($childrenIds);
            foreach ($children as $child) {
                $parents[$status->getId()][] = $child->getId();
            }
            $statusesTree[] = $status->getId();
        }
        if (empty($parents)) { // after update to v2.0.61 statuses do not organized in tree yet, so we use old method
            $this->statusTree = $statusesTree;
        } else {
            $this->resetTree();
            $parentId = $this->rmaConfig->getDefaultStatus();
            $activeBranch = $parentId == $this->currentStatusId;
            $lockTree = false;
            $this->createBrach($parents, $parentId, $activeBranch, $lockTree);
        }

        return $this->statusTree;
    }

    /**
     * @param array $list
     * @param int   $parentId
     * @param bool  $activeBranch
     * @param bool  $lockTree
     */
    private function createBrach(&$list, $parentId, &$activeBranch, &$lockTree)
    {
        $this->tmpStatusTree[$parentId] = 1;
        if(isset($list[$parentId])) {
            $isLastChild = true;
            foreach ($list[$parentId] as $childId) {
                if ($childId > 0 && !isset($this->tmpStatusTree[$childId])) {
                    $isLastChild = false;
                    if ($childId == $this->currentStatusId) {
                        $activeBranch = true;
                        $findCurrentParent = false;
                        // remove nodes of previous branch
                        foreach ($this->tmpStatusTree as $k => $v) {
                            if ($findCurrentParent) {
                                unset($this->tmpStatusTree[$k]);
                            }
                            if ($k == $parentId) {
                                $findCurrentParent = true;
                            }
                        }
                    }
                    $this->createBrach($list, $childId, $activeBranch, $lockTree);
                    // when found active branch, takes only first child and do not build other branches
                    if ($activeBranch) {
                        break;
                    }
                }
            }
            if ($isLastChild && $activeBranch) {
                $lockTree = true;
            }
        } elseif ($activeBranch) { // we found last child of current branch
            $lockTree = true;
        }
        if ($lockTree) {
            array_unshift($this->statusTree, $parentId);
        }
    }

    /**
     * @param array $ids
     * @return StatusInterface[]|Collection
     */
    private function getChildrenStatuses($ids)
    {
        $statuses = $this->statusRepository->getCollection();
        $statuses->addActiveFilter();
        $statuses->getSelect()
            ->where('status_id in (?)', $ids)
            ->order(StatusInterface::KEY_IS_MAIN_BRANCH . ' desc')
            ->order(StatusInterface::KEY_SORT_ORDER . ' asc');

        return $statuses;
    }

    /**
     * @return void
     */
    private function resetTree()
    {
        $this->statusTree    = [];
        $this->tmpStatusTree = [];
    }

    /**
     * @return array
     */
    public function getDiagramTree()
    {
        $startId = $this->rmaConfig->getDefaultStatus();
        $this->statusDiagramCollection = $this->statusRepository->getCollection();
        $this->statusDiagramCollection->getSelect()->order('IF(status_id = ' . $startId . ', 1, 0) DESC');

        $tree = [];
        $position = 1;
        $status = $this->statusDiagramCollection->getFirstItem();
        $tree[$status->getId()] = [
            'name'     => $status->getName(),
            'class'    => $status->getColor(),
            'position' => $position,
            'childIds' => $status->getChildrenIds(),
            'relation' => [],
        ];

        $this->addNode($tree, $position, $status->getId());

        $this->checkUnconnectedNodes($tree, $position);

        return $tree;
    }

    /**
     * @param array $tree
     * @param int   $position
     * @param int   $statusId
     */
    private function addNode(&$tree, &$position, $statusId)
    {
        foreach ($tree[$statusId]['childIds'] as $childId) {
            if (!$childId) {
                continue;
            }
            if (isset($tree[$childId])) {
                $tree[$statusId]['relation'][] = $tree[$childId]['position'] - $tree[$statusId]['position'];
            } else {
                $position++;
                $status = $this->statusDiagramCollection->getItemById($childId);
                if ($status) {
                    $tree[$childId] = [
                        'name'     => $status->getName(),
                        'class'    => $status->getColor(),
                        'position' => $position,
                        'childIds' => $status->getChildrenIds(),
                        'relation' => [],
                    ];
                    $tree[$statusId]['relation'][] = count($tree) - $tree[$statusId]['position'];
                    $this->addNode($tree, $position, $status->getId());
                } else {// removed status
                    $tree[$childId] = [
                        'name'     => __('Removed Status'),
                        'class'    => '',
                        'position' => $position,
                        'childIds' => [],
                        'relation' => [],
                    ];
                    $tree[$statusId]['relation'][] = count($tree) - $tree[$statusId]['position'];
                }
            }
        }
    }

    /**
     * @param array $tree
     * @param int   $position
     */
    private function checkUnconnectedNodes(&$tree, &$position)
    {
        $allIds = $this->statusDiagramCollection->getAllIds();
        $missedIds = array_diff($allIds, array_keys($tree));

        foreach ($missedIds as $missedId) {
            if (isset($tree[$missedId])) {
                continue;
            }
            $position++;
            $status = $this->statusDiagramCollection->getItemById($missedId);
            if ($status) {
                $tree[$missedId] = [
                    'name'     => $status->getName(),
                    'class'    => $status->getColor(),
                    'position' => $position,
                    'childIds' => $status->getChildrenIds(),
                    'relation' => [],
                ];
            } else {
                $tree[$missedId] = [
                    'name'     => __('Removed Status'),
                    'class'    => '',
                    'position' => $position,
                    'childIds' => [],
                    'relation' => [],
                ];
            }
            $this->addNode($tree, $position, $status->getId());
        }
    }
}