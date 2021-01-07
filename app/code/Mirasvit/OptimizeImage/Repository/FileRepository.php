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



namespace Mirasvit\OptimizeImage\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\OptimizeImage\Api\Data\FileInterface;
use Mirasvit\OptimizeImage\Api\Data\FileInterfaceFactory;
use Mirasvit\OptimizeImage\Model\ResourceModel\File\CollectionFactory;

class FileRepository
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
     * @var FileInterfaceFactory
     */
    private $factory;

    public function __construct(
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        FileInterfaceFactory $factory
    ) {
        $this->entityManager     = $entityManager;
        $this->collectionFactory = $collectionFactory;
        $this->factory           = $factory;
    }

    /**
     * @return FileInterface[]|\Mirasvit\OptimizeImage\Model\ResourceModel\File\Collection
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * @return FileInterface
     */
    public function create()
    {
        return $this->factory->create();
    }

    /**
     * @param int $id
     *
     * @return FileInterface|false
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
     * @param string $relativePath
     *
     * @return FileInterface|false
     */
    public function getByRelativePath($relativePath)
    {
        /** @var \Mirasvit\OptimizeImage\Model\File $model */
        $model = $this->create();
        $model->load($relativePath, FileInterface::RELATIVE_PATH);

        if (!$model->getId()) {
            return false;
        }

        return $model;
    }

    /**
     * @param FileInterface $model
     *
     * @return FileInterface
     */
    public function save(FileInterface $model)
    {
        return $this->entityManager->save($model);
    }

    /**
     * @param FileInterface $model
     */
    public function delete(FileInterface $model)
    {
        $this->entityManager->delete($model);
    }
}
