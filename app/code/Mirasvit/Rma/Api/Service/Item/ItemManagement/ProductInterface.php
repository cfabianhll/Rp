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



namespace Mirasvit\Rma\Api\Service\Item\ItemManagement;


interface ProductInterface
{
    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct(\Mirasvit\Rma\Api\Data\ItemInterface $item);

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getExchangeProduct(\Mirasvit\Rma\Api\Data\ItemInterface $item);

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @param string                               $imageId
     * @param array                                $attributes
     *
     * @return \Magento\Catalog\Helper\Image
     */
    public function getImage($item, $imageId, $attributes = []);
}
