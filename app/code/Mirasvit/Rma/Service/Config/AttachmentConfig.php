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



namespace Mirasvit\Rma\Service\Config;

class AttachmentConfig implements \Mirasvit\Rma\Api\Config\AttachmentConfigInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * AttachmentConfig constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param null|int $store
     * @return array|mixed
     */
    public function getFileAllowedExtensions($store = null)
    {
        if (!$extensions = $this->scopeConfig->getValue(
            'rma/general/file_allowed_extensions',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        )) {
            return [];
        }
        $extensions = explode(',', $extensions);
        $extensions = array_map('trim', $extensions);
        $extensions = array_map('strtolower', $extensions);

        return $extensions;
    }

    /**
     * @param null|int $store
     * @return array|mixed
     */
    public function getShippingLabelsAllowedExtensions($store = null)
    {
        if (!$extensions = $this->scopeConfig->getValue(
            'rma/general/shipping_label_allowed_extensions',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        )) {
            return [];
        }
        $extensions = explode(',', $extensions);
        $extensions = array_map('trim', $extensions);
        $extensions = array_map('strtolower', $extensions);

        return $extensions;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileSizeLimit($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/general/file_size_limit',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }
}
