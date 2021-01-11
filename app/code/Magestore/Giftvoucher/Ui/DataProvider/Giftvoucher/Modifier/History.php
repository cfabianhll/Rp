<?php
/**
 * Copyright © 2017 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Giftvoucher\Ui\DataProvider\Giftvoucher\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Framework\View\Element\UiComponent\DataProvider\Sanitizer;

/**
 * Gift voucher modifier - History
 */
class History implements ModifierInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * History constructor.
     * @param \Magento\Framework\Registry $coreRegistry
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        UrlInterface $urlBuilder
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Check Class Sanitizer exist method extractConfig
     *
     * @return boolean
     * @throws \Exception
     */
    private function isExistDisableTmpl()
    {
        try {
            if (class_exists(Sanitizer::class)) {
                $nameClass = Sanitizer::class;
                $nameMethod = 'extractConfig';
                return method_exists($nameClass, $nameMethod);
            } else {
                return false;
            }
        } catch (\Exception $error) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function modifyMeta(array $meta)
    {
        if (!$this->coreRegistry->registry('giftvoucher_data')->getId()) {
            return $meta;
        }
        $meta['giftcode_history'] = [
            'children' => [
                'review_listing' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender' => true,
                                'componentType' => 'insertListing',
                                'dataScope' => 'giftcard_code_history_listing',
                                'externalProvider' => 'giftcard_code_history_listing'
                                    . '.' . 'giftcard_code_history_listing_data_source',
                                'selectionsProvider' => 'giftcard_code_history_listing'
                                    . '.' . 'giftcard_code_history_listing'
                                    . '.' . 'giftcode_columns'
                                    . '.' . 'ids',
                                'ns' => 'giftcard_code_history_listing',
                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink' => false,
                                'behaviourType' => 'simple',
                                'externalFilterMode' => true,
                                'imports' => [
                                    'giftvoucherId' => '${ $.provider }:data.current_giftvoucher_id',
                                ],
                                'exports' => [
                                    'giftvoucherId' => '${ $.externalProvider }:params.current_giftvouchert_id',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Transaction History'),
                        'collapsible' => true,
                        'opened' => false,
                        'componentType' => Fieldset::NAME,
                    ],
                ],
            ],
        ];

        if ($this->isExistDisableTmpl()) {
            $meta['giftcode_history']['children']['review_listing']['arguments']
            ['data']['config']['imports']['__disableTmpl']['giftvoucherId'] = false;

            $meta['giftcode_history']['children']['review_listing']['arguments']
            ['data']['config']['exports']['__disableTmpl']['giftvoucherId'] = false;
        }

        return $meta;
    }
    
    /**
     * @inheritDoc
     */
    public function modifyData(array $data)
    {
        $id = $this->coreRegistry->registry('giftvoucher_data')->getId();
        $data[$id]['current_giftvoucher_id'] = $id;
        return $data;
    }
}
