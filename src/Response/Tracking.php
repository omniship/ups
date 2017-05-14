<?php

namespace Omniship\Ups\Response;

class Tracking
{
    public function transform($data)
    {
        $return = [];

        //
        $return['tracking_number'] = $data['ShipmentIdentificationNumber'] ?? null;

        $return['tracking_status'] = [
            // "status" => "DELIVERED",
            // "status_details" => "Your shipment has been delivered at the destination mailbox.",
            // "status_date" => "2016-07-23T13:03:00Z",
            // "location" => [
            //   "city" => "Spotsylvania",
            //   "state" => "VA",
            //   "zip" => "22551",
            //   "country" => "US"
            // ]
        ];

        // Do we have a scheduled delivery?
        if (isset($data['ScheduledDeliveryDate'])) {
            $return['estimated_delivery'] = date('Y-m-d\TH:i:s\Z', strtotime($data['ScheduledDeliveryDate']));
        }

        //
        // $date = $act['Date'] ?? '';
        // $time = $act['Time'] ?? '';
        //
        // $return['date'] = date('Y-m-d\TH:i:s\Z', strtotime($date.$time));

        //
        $return['tracking_history'] = [];

        $packages = [];

        // Single package?
        if (isset($data['Package']['TrackingNumber'])) {
            $packages[] = $data['Package'];
        }

        // Multiple packages?
        if (isset($data['Package'][0])) {
            $packages = $data['Package'];
        }

        $history = [];

        $codes = [
            'I' => 'In Transit',
            'D' => 'Delivered',
            'X' => 'Exception',
            'P' => 'Pickup',
            'M' => 'Manifest Pickup for Mail Innovations',
        ];

        foreach ($packages as $package) {
            if (isset($package['TrackingNumber'])) {
                foreach ($package['Activity'] as $activity) {
                    $status = $activity['Status']['StatusType'];
                    //var_dump($activity);die;
                    $history[] = [
                        'status' => $codes[$status['Code']],
                        'status_details' => $status['Description'],
                    ];
                }
                #var_dump($package['Activity']);
                // $return['tracking_history'][] = [
                // //     "status": "TRANSIT",
                // //     "status_details": "Your shipment has been accepted.",
                // //     "status_date": "2016-07-21T15:33:00Z",
                // //     "location": [
                // //       "city": "Las Vegas",
                // //       "state": "NV",
                // //       "zip": "89101",
                // //       "country": "US"
                // //   ]
                //
                //     'tracking_number' => $package['TrackingNumber'],
                //     'destination' => '',
                //     'activity' => '',
                // ];
            }
        }

        $return['tracking_history'] = $history;

        return $return;

        $activity = $data['Package']['Activity'];

        var_dump($activity);

        #$firstLog = $activity[0];

        #$estimatedDeliveryDate =
        return [
            #'number'   =>
            #'estimated_delivery_date' =>
            #'status'   => $data['CurrentStatus']['Description'],
            #'activity' => $this->includeActivity($data['Activity']),
        ];
    }

    protected function includeActivity($activity)
    {
        $data = [];

        foreach ($activity as $ac) {
            $data[] = [
                'message'  => $ac['Description'],
                'date'     => $ac['Date'],
                // 'location' => $ac['ActivityLocation'],
            ];
        }

        return $data;
    }
}
