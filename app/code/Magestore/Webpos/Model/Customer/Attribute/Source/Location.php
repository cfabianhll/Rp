<?php

namespace Magestore\Webpos\Model\Customer\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magestore\Webpos\Model\ResourceModel\Location\Location\CollectionFactory;

/**
 * Class location attribute source
 */
class Location extends AbstractSource
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var CollectionFactory
     */
    protected $_webposLocation;

    /**
     * Constructor for location attribute source
     *
     * @param CollectionFactory $webposLocation
     */
    public function __construct(
        CollectionFactory $webposLocation
    ) {
        $this->_webposLocation = $webposLocation;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        $collection = $this->_webposLocation->create();

        if ($collection->getSize()) {
            foreach ($collection->getItems() as $location) {
                $this->options[] = ['value' => $location->getId(), 'label' => $location->getName()];
            }
        }

        return $this->options;
    }
}
