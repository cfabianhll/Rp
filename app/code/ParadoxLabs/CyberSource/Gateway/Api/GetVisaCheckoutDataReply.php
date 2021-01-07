<?php

namespace ParadoxLabs\CyberSource\Gateway\Api;

class GetVisaCheckoutDataReply
{
    /**
     * @var int $reasonCode
     */
    protected $reasonCode;

    /**
     * @param int $reasonCode
     */
    public function __construct($reasonCode)
    {
        $this->reasonCode = $reasonCode;
    }

    /**
     * @return int
     */
    public function getReasonCode()
    {
        return $this->reasonCode;
    }

    /**
     * @param int $reasonCode
     * @return \ParadoxLabs\CyberSource\Gateway\Api\GetVisaCheckoutDataReply
     */
    public function setReasonCode($reasonCode)
    {
        $this->reasonCode = $reasonCode;

        return $this;
    }
}
