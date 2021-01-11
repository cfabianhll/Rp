<?php

/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Controller\Adminhtml\Mui;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Controller\Adminhtml\Index\Render as CoreRender;
use Magento\Ui\Model\UiComponentTypeResolver;
use Psr\Log\LoggerInterface;

/**
 * Render select source product listing
 */
class Render extends CoreRender implements HttpPostActionInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Render constructor.
     *
     * @param Context $context
     * @param UiComponentFactory $factory
     * @param UiComponentTypeResolver $contentTypeResolver
     * @param SerializerInterface $serializer
     * @param JsonFactory|null $resultJsonFactory
     * @param Escaper|null $escaper
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Context $context,
        UiComponentFactory $factory,
        UiComponentTypeResolver $contentTypeResolver,
        SerializerInterface $serializer,
        JsonFactory $resultJsonFactory = null,
        Escaper $escaper = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct(
            $context,
            $factory,
            $contentTypeResolver,
            $resultJsonFactory,
            $escaper,
            $logger
        );
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $params = $this->_request->getParams();
        if (isset($params['filters_modifier']) && is_string($params['filters_modifier'])) {
            $filtersModifier = $this->serializer->unserialize($params['filters_modifier'], true);
            $params['filters_modifier'] = $this->processFiltersModifier($filtersModifier);
            $this->_request->setParams($params);
        }
        return parent::execute();
    }

    /**
     * Process filters modifier after json_decode
     *
     * @param array $filtersModifier
     * @return mixed[]
     */
    public function processFiltersModifier(array $filtersModifier)
    {
        $result = [];
        foreach ($filtersModifier as $key => $modifier) {
            if (is_array($modifier)) {
                $modifier = $this->processFiltersModifier($modifier);
                if (!empty($modifier)) {
                    $result[$key] = $modifier;
                }
            } else {
                $result[$key] = $modifier;
            }
        }
        return $result;
    }
}
