<?php

/*

$carrier = new Ups(..)

//
$carrier->tracking()->track(:trackingNumber, :requestOption = 'activity')
$carrier->tracking()->trackByReference(:referenceNumber, :requestOption = 'activity')

*/


namespace Omniship\Ups;

use Omniship\Common\AbstractCarrier;

class Carrier extends AbstractCarrier
{
    protected $liveEndpoint = 'https://onlinetools.ups.com';

    protected $testEndpoint = 'https://wwwcie.ups.com';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'UPS';
    }


    public function requirements()
    {
        return [
            'key', 'login', 'password',
        ];
    }

    public function tracking()
    {
        ## Need to pass the configuration and such
        return new Tracking($this);
    }








    protected $resources = [
        'rates' => 'ups.app/xml/Rate',
        'track' => 'ups.app/xml/Track',
        'shipconfirm' => 'ups.app/xml/ShipConfirm',
        'shipaccept' => 'ups.app/xml/ShipAccept',
        'shipvoid' => 'ups.app/xml/Void',
        'valid_address' => 'ups.app/xml/AV',
        'valid_address_street' => 'ups.app/xml/XAV',
    ];



    /**
     * Validate a UPS tracking number by doing a sanity check, performing the UPS check
     * bit algorithm, and verifying the result.
     *
     * I couldn't find any official documentation regarding the check bit algorithm, but
     * was able to find a useful description of it at the link included in this method's
     * comment.
     *
     * @link http://answers.google.com/answers/threadview/id/207899.html
     * @inheritdoc
     */
    public function isTrackingNumber($trackingNumber)
    {
        $trackingNumber = strtoupper($trackingNumber);

        if (!ctype_alnum($trackingNumber) || strpos($trackingNumber, '1Z') !== 0 || strlen($trackingNumber) != 18) {
            return false;
        }

        $testDigits = strtr(substr($trackingNumber, 2), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', '23456789012345678901234567');

        $sum = 0;

        for ($i = 0; $i < 15; $i++) {
            if ($i % 2) {
                $sum += $testDigits[$i];
            }

            $sum += $testDigits[$i];
        }

        $checkDigit = ($sum % 10);

        $checkDigit = ($checkDigit == 0) ? $checkDigit : (10 - $checkDigit);

        return ($checkDigit == $testDigits[15]);
    }

    public function getAvailableParameters()
    {

        'from' => 'required|address',
        'to'   => 'required|address',
        'to.name',
        'to.address_line_1',
        'to.address_line_2'
    }

    public function getParametersSynonyms() : array
    {
        return [
            'customsDescription' => 'invoiceLineDescription',
            'customsQuantity' => 'invoiceLineNumber',
            'customsValue' => 'invoiceLineValue',
            'customsPartNumber' => 'invoiceLinePartNumber',
            'customsOriginCountry' => 'invoiceLineOriginCountryCode',
        ];
    }

    /**
     *
     *
     * @return array
     */
    public function getReferenceCodes() : array # add to the carrier interface
    {
        return [
            'AJ', // Accounts Receivable Customer Account
            'AT', // Appropriation Number
            'BM', // Bill of Lading Number
            '9V', // Collect on Delivery (COD) Number
            'ON', // Dealer Order Number
            'DP', // Department Number
            '3Q', // Food and Drug Administration (FDA) Product Code
            'IK', // Invoice Number
            'MK', // Manifest Key Number
            'MJ', // Model Number
            'PM', // Part Number
            'PC', // Production Code
            'PO', // Purchase Order Number
            'RQ', // Purchase Request Number
            'RZ', // Return Authorization Number
            'SA', // Salesperson Number
            'SE', // Serial Number
            'ST', // Store Number
            'TN', // Transaction Reference Number
        ];
    }
}
