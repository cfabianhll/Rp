<?php
namespace Trootech\General\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{    
    

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
    ) {
        $this->addressRenderer = $addressRenderer;
        parent::__construct($context);
    }

    /**
     * Returns string with formatted address
     *
     * @param Address $address
     * @return null|string
     */
    public function getFormattedAddress(\Magento\Sales\Model\Order\Address $address)
    {
        return $this->addressRenderer->format($address, 'html');
    }
    
}
