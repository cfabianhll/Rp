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



namespace Mirasvit\OptimizeJs\Api\Repository;

use Mirasvit\OptimizeJs\Api\Data\BundleFileInterface;

interface BundleFileRepositoryInterface
{
    /**
     * @return \Mirasvit\OptimizeJs\Model\ResourceModel\BundleFile\Collection|BundleFileInterface[]
     */
    public function getCollection();

    /**
     * @return BundleFileInterface
     */
    public function create();

    /**
     * @param BundleFileInterface $model
     *
     * @return BundleFileInterface
     */
    public function save(BundleFileInterface $model);

    /**
     * @param BundleFileInterface $model
     *
     * @return BundleFileInterface
     */
    public function ensure(BundleFileInterface $model);

    /**
     * @param int $id
     *
     * @return BundleFileInterface|false
     */
    public function get($id);

    /**
     * @param BundleFileInterface $model
     *
     * @return bool
     */
    public function delete(BundleFileInterface $model);
}
