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


namespace Mirasvit\Rma\Plugin\Rma;
use Mirasvit\Rma\Api\Data\RmaInterface;

class SetDefaultsBeforeCreatePlugin
{
    /**
     * @var \Mirasvit\Rma\Api\Config\RmaConfigInterface
     */
    private $rmaConfig;
    /**
     * @var \Mirasvit\Core\Api\TextHelperInterface
     */
    private $mstCoreString;

    /**
     * SetDefaultsBeforeCreatePlugin constructor.
     * @param \Mirasvit\Core\Api\TextHelperInterface $mstCoreString
     * @param \Mirasvit\Rma\Api\Config\RmaConfigInterface $rmaConfig
     */
    public function __construct(
        \Mirasvit\Core\Api\TextHelperInterface $mstCoreString,
        \Mirasvit\Rma\Api\Config\RmaConfigInterface $rmaConfig
    ) {
        $this->mstCoreString = $mstCoreString;
        $this->rmaConfig     = $rmaConfig;
    }

    /**
     * @param RmaInterface $rma
     * @return void
     */
    public function beforeSave(RmaInterface $rma)
    {
        if ($rma->getId()) {
            return;
        }
        $rma->setGuestId(
            hash('sha256',
                (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT).
                $this->mstCoreString->generateRandString(10)
            )
        );
        $rma->setIsAdminRead(true);

        if (!$rma->getStatusId()) {
            $rma->setStatusId($this->rmaConfig->getDefaultStatus());
        }
        if (!$rma->getUserId()) {
            $rma->setUserId($this->rmaConfig->getDefaultUser());
        }
    }
}