<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\App\Response\Http\FileFactory;

/**
 * Class Report
 */
abstract class Report extends Action
{
    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * Report constructor.
     * @param Action\Context $context
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Action\Context $context,
        FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->_fileFactory = $fileFactory;
    }

    const ADMIN_RESOURCE = 'Amasty_MultiInventory::lowstock';
}
