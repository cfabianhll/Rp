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




namespace Mirasvit\OptimizeJs\Preference\Deploy\Package;

use Magento\Deploy\Package\BundleInterface;

/** mp comment start **/
$path = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Framework\Filesystem\DirectoryList')->getRoot();
$path1 = $path . '/vendor/magento/module-deploy/Package/BundleInterface.php';
$path2 = $path . '/app/code/Magento/Deploy/Package/BundleInterface.php';

if (file_exists($path1) || file_exists($path2)) {
    /** mp comment end **/
    interface BundleParentInterface extends BundleInterface
    {
    }
    /** mp comment start **/
} else {
    interface BundleParentInterface
    {
    }
}
/** mp comment end **/
