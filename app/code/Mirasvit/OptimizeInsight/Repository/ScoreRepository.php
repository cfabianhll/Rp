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



namespace Mirasvit\OptimizeInsight\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\OptimizeInsight\Api\Data\ScoreInterface;
use Mirasvit\OptimizeInsight\Api\Data\ScoreInterfaceFactory;
use Mirasvit\OptimizeInsight\Model\ResourceModel\Score\CollectionFactory;

class ScoreRepository
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ScoreInterfaceFactory
     */
    private $factory;

    public function __construct(
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        ScoreInterfaceFactory $logFactory
    ) {
        $this->entityManager     = $entityManager;
        $this->collectionFactory = $collectionFactory;
        $this->factory           = $logFactory;
    }

    /**
     * @return ScoreInterface[]|\Mirasvit\OptimizeInsight\Model\ResourceModel\Score\Collection
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * @return ScoreInterface
     */
    public function create()
    {
        return $this->factory->create();
    }

    /**
     * @param int $id
     *
     * @return ScoreInterface|false
     */
    public function get($id)
    {
        $model = $this->create();
        $model = $this->entityManager->load($model, $id);

        if (!$model->getId()) {
            return false;
        }

        return $model;
    }

    /**
     * @param ScoreInterface $model
     *
     * @return ScoreInterface
     */
    public function save(ScoreInterface $model)
    {
        return $this->entityManager->save($model);
    }

    /**
     * @param ScoreInterface $model
     */
    public function delete(ScoreInterface $model)
    {
        $this->entityManager->delete($model);
    }
}
