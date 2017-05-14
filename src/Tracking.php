<?php

namespace Omniship\Ups;

use FluidXml\FluidXml;

class Tracking extends Api
{
    protected $trackingNumber;

    protected $referenceNumber;

    protected $requestOption;

    public function track($trackingNumber, $requestOption = '3')
    {
        $this->trackingNumber = $trackingNumber;

        $this->requestOption = $requestOption;

        return $this->execute();
    }

    public function trackByReference($referenceNumber, $requestOption = '3')
    {
        $this->referenceNumber = $referenceNumber;

        $this->requestOption = $requestOption;

        return $this->execute();
    }

    protected function getData()
    {
        $xml = new FluidXml('TrackRequest');

        $request = $xml->addChild('Request', true);
            $request
                ->addChild('TransactionReference', true)
                    ->addChild('CustomerContext', 'Omniship')
            ;

            $request->addChild('RequestAction', 'Track');

            if ($this->requestOption !== null) {
                $request->addChild('RequestOption', $this->requestOption);
            }

        if ($this->trackingNumber !== null) {
            $xml->addChild('TrackingNumber', $this->trackingNumber);
        }

        // if ($this->isMailInnovations()) {
        //     $xml->addChild($xml->createElement('IncludeMailInnovationIndicator'));
        // }

        if ($this->referenceNumber !== null) {
            $xml
                ->addChild('ReferenceNumber', true)
                    ->addChild('Value', $this->referenceNumber)
            ;
        }

        // if ($this->shipperNumber !== null) {
        //     $xml->addChild('ShipperNumber', $this->shipperNumber);
        // }

        // if ($this->beginDate !== null || $this->endDate !== null) {
        //     $DateRange = $xml->addChild('PickupDateRange', true);
        //
        //     if (null !== $this->beginDate) {
        //         $beginDate = $this->beginDate->format('Ymd');
        //         $DateRange->addChild('BeginDate', $beginDate);
        //     }
        //     if (null !== $this->endDate) {
        //         $endDate = $this->endDate->format('Ymd');
        //         $DateRange->addChild('EndDate', $endDate);
        //     }
        //     $xml->addChild($DateRange);
        // }

        return $xml->xml();
    }

    protected function getResponse(array $data)
    {
        return (new Response\Tracking)->transform($data['Shipment']);
    }

    protected function getEndpoint()
    {
        return 'Track';
    }
}
