<?php
/**
 * Class Timereload Doc Comment
 *
 * PHP version 7
 *
 * @category Sparsh Technologies
 * @package  Sparsh_MaintenanceMode
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */

namespace Sparsh\MaintenanceMode\Controller\Index;

/**
 * Class Timereload Doc Comment
 *
 * @category Sparsh Technologies
 * @package  Sparsh_MaintenanceMode
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Timereload extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Sparsh\MaintenanceMode\Helper\Data
     */
    protected $helperData;

    /**
     * CacheTypelist
     *
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * Cachepool
     *
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $cacheFrontendPool;

    /**
     * Timereload constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param \Sparsh\MaintenanceMode\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Sparsh\MaintenanceMode\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        return parent::__construct($context);
    }

    /**
     * Timereload Action
     *
     * @return mixed
     */
    public function execute()
    {
        $this->helperData->getCurrentTime();
        $types = [
                    'config',
                    'layout',
                    'block_html',
                    'collections',
                    'reflection',
                    'db_ddl',
                    'eav',
                    'config_integration',
                    'config_integration_api',
                    'full_page',
                    'translate',
                    'config_webservice'
                ];
        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
        $this->_redirect('');
    }
}
