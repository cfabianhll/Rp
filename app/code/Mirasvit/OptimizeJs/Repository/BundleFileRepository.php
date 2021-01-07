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



namespace Mirasvit\OptimizeJs\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\OptimizeJs\Api\Data\BundleFileInterface;
use Mirasvit\OptimizeJs\Api\Data\BundleFileInterfaceFactory;
use Mirasvit\OptimizeJs\Api\Repository\BundleFileRepositoryInterface;
use Mirasvit\OptimizeJs\Model\ResourceModel\BundleFile\CollectionFactory;

class BundleFileRepository implements BundleFileRepositoryInterface
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
     * @var BundleFileInterfaceFactory
     */
    private $factory;

    public function __construct(
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        BundleFileInterfaceFactory $logFactory
    ) {
        $this->entityManager     = $entityManager;
        $this->collectionFactory = $collectionFactory;
        $this->factory           = $logFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->factory->create();
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function save(BundleFileInterface $model)
    {
        return $this->entityManager->save($model);
    }

    /**
     * {@inheritdoc}
     */
    public function ensure(BundleFileInterface $model)
    {
        $item = $this->getCollection()
            ->addFieldToFilter(BundleFileInterface::AREA, $model->getArea())
            ->addFieldToFilter(BundleFileInterface::LAYOUT, $model->getLayout())
            ->addFieldToFilter(BundleFileInterface::THEME, $model->getTheme())
            ->addFieldToFilter(BundleFileInterface::LOCALE, $model->getLocale())
            ->addFieldToFilter(BundleFileInterface::FILENAME, $model->getFilename())
            ->getFirstItem();

        return $item->getId() ? $item : $this->save($model);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(BundleFileInterface $model)
    {
        $this->entityManager->delete($model);
    }
}
