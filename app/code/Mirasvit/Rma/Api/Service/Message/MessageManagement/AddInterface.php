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



namespace Mirasvit\Rma\Api\Service\Message\MessageManagement;


interface AddInterface
{
    /**
     * @param \Mirasvit\Rma\Api\Service\Performer\PerformerInterface $performer
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @param string $messageText
     * @param array $params
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Mirasvit\Rma\Model\Message
     */
    public function addMessage(
        \Mirasvit\Rma\Api\Service\Performer\PerformerInterface $performer,
        \Mirasvit\Rma\Api\Data\RmaInterface $rma,
        $messageText,
        $params = []
    );
}