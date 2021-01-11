<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Block\Adminhtml\Stocktaking;

use Magestore\Stocktaking\Service\Adminhtml\Stocktaking\Edit\GetCurrentStocktakingService;

/**
 * Stocktaking - Abstract Stocktaking
 */
class AbstractStocktaking
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var GetCurrentStocktakingService
     */
    protected $getCurrentStocktakingService;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * AbstractStocktaking constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param GetCurrentStocktakingService $getCurrentStocktakingService
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        GetCurrentStocktakingService $getCurrentStocktakingService
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->request = $context->getRequest();
        $this->authorization = $context->getAuthorization();
        $this->getCurrentStocktakingService = $getCurrentStocktakingService;
    }

    /**
     * Get Current Stocktaking
     *
     * @return \Magestore\Stocktaking\Api\Data\StocktakingInterface
     */
    public function getStocktaking(): ?\Magestore\Stocktaking\Api\Data\StocktakingInterface
    {
        return $this->getCurrentStocktakingService->getCurrentStocktaking();
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * Is Allowed
     *
     * @param string $permission
     * @return bool
     */
    public function isAllowed(string $permission): bool
    {
        return $this->authorization->isAllowed($permission);
    }
}
