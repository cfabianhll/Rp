<?php

namespace MagePsycho\RegionCityPro\Plugin\Model\Checkout;

use Magento\Checkout\Model\PaymentInformationManagement;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use MagePsycho\RegionCityPro\Helper\Data as RegionCityProHelper;

/**
 * @category   MagePsycho
 * @package    MagePsycho_RegionCityPro
 * @author     Raj KB <magepsycho@gmail.com>
 * @website    https://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PaymentInformationManagementPlugin
{
    /**
     * @var RegionCityProHelper
     */
    private $regionCityProHelper;

    public function __construct(
        RegionCityProHelper $regionCityProHelper
    ) {
        $this->regionCityProHelper = $regionCityProHelper;
    }

    public function aroundSavePaymentInformation(
        PaymentInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $this->regionCityProHelper->log(__METHOD__, true);
        if ($this->regionCityProHelper->isFxnSkipped()) {
            return $proceed($cartId, $paymentMethod, $billingAddress);
        }

        $extensionAttributes = $billingAddress->getExtensionAttributes();
        if ($extensionAttributes) {
            $cityId = (int) $extensionAttributes->getCityId();
            $this->regionCityProHelper->log('$cityId::' . $cityId);
            if ($cityId) {
                $billingAddress->setCityId($cityId);
            }
        }
        return $proceed($cartId, $paymentMethod, $billingAddress);
    }
}
