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



namespace Mirasvit\Rma\Setup\UpgradeData;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData1017 implements UpgradeDataInterface
{
    protected $roleCollectionFactory;

    protected $rulesFactory;

    protected $policyInterface;

    protected $aclRetriever;

    public function __construct(
        \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $roleCollectionFactory,
        \Magento\Authorization\Model\RulesFactory $rulesFactory,
        \Magento\Framework\Authorization\PolicyInterface $policyInterface,
        \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever
    ) {
        $this->roleCollectionFactory = $roleCollectionFactory;
        $this->rulesFactory          = $rulesFactory;
        $this->policyInterface       = $policyInterface;
        $this->aclRetriever          = $aclRetriever;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $rmaResources = ['Mirasvit_Rma::actions', 'Mirasvit_Rma::add', 'Mirasvit_Rma::delete'];
        $roles        = $this->roleCollectionFactory->create();
        $roles->addFieldToFilter('role_type', 'G');

        foreach ($roles as $role) {
            $allowedResources     = $this->aclRetriever->getAllowedResourcesByRole($role->getId());
            $isAccessToRmaAllowed = $this->policyInterface->isAllowed($role->getId(), 'Mirasvit_Rma::rma');

            if ($isAccessToRmaAllowed && $allowedResources != ['Magento_Backend::all']) {
                foreach ($rmaResources as $rmaResource) {
                    $allowedResources[] = $rmaResource;
                }

                $this->rulesFactory->create()->setRoleId($role->getId())
                    ->setResources($allowedResources)
                    ->saveRel();
            }
        }

        $setup->endSetup();
    }
}