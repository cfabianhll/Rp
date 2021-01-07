<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Helper;

use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Distance
 */
class Distance
{
    /**
     * @var Curl
     */
    private $clientUrl;

    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var System
     */
    private $system;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Distance constructor.
     * @param Curl $clientUrl
     * @param DecoderInterface $jsonDecoder
     * @param ManagerInterface $messageManager
     * @param System $system
     */
    public function __construct(
        Curl $clientUrl,
        DecoderInterface $jsonDecoder,
        ManagerInterface $messageManager,
        System $system
    ) {
        $this->clientUrl = $clientUrl;
        $this->jsonDecoder = $jsonDecoder;
        $this->system = $system;
        $this->messageManager = $messageManager;
    }

    /**
     * Prepare urlencode string to send it as parameter to google
     *
     * @param array $data
     * @return string
     */
    public function prepareAddressForGoogle($data)
    {
        $address = "";
        $arrayCodes = ['country', 'country_id', 'state', 'region', 'city', 'address', 'street', 'zip'];

        foreach ($arrayCodes as $code) {
            if (isset($data[$code]) && !empty($data[$code])) {
                if ($code == 'region') {
                    /** If address was save, region will be an array on M2.2.* */
                    if (is_array($data[$code])) {
                        $data[$code] = (string)$data[$code][$code];
                    }
                    /** If address was saved, region will be an object on M2.1.* */
                    if (is_object($data[$code])) {
                        $data[$code] = (string)$data[$code]->getRegion();
                    }
                    if (empty($data[$code])) {
                        continue;
                    }
                }
                if (strlen($address) > 0) {
                    $address .= " ";
                }
                $address .= $data[$code];
            }
        }

        return urlencode($address);
    }

    /**
     * Send request to google to get coordinates by address
     *
     * @param $address
     *
     * @return bool|array
     * @throws \Zend_Json_Exception
     */
    public function getCoordinatesByAddress($address)
    {
        if ($this->system->isMultiEnabled()
            && !$this->system->isUseGoogleForDistance()
            && $this->system->getGoogleMapsKey()
        ) {
            $key = "&key=" . $this->system->getGoogleMapsKey();
            $url = $this->system->getGeocodeUrl() . 'address=' . $address . $key;

            $this->clientUrl->write(
                \Zend_Http_Client::GET,
                $url,
                '1.1',
                []
            );
            $googleResponse = $this->clientUrl->read();
            $responseBody = \Zend_Http_Response::extractBody($googleResponse);
            $response = \Zend_Json::decode($responseBody);

            return $this->checkResponse($response);
        }

        return false;
    }

    /**
     * @param array $response
     *
     * @return mixed
     */
    private function checkResponse($response)
    {
        if (!empty($response)) {
            if ($response['status'] == 'OK') {
                if (isset($response['results'][0]['geometry']['location'])) {
                    return $response['results'][0]['geometry']['location'];
                } else {
                    $this->messageManager->addErrorMessage(__('Location was not detected.'));
                }
            } elseif ($response['status'] == 'REQUEST_DENIED') {
                $this->messageManager->addErrorMessage(__($response['error_message']));
            } elseif ($response['status'] == 'INVALID_REQUEST') {
                $this->messageManager->addNoticeMessage(__('Invalid request. Missing the address parameter.'));
            }
        }

        return false;
    }
}
