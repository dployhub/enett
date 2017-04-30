<?php

namespace Dploy\Enett\Tests;

use Dploy\Enett\Models\EnettResponse;
use Dploy\Enett\Models\ProcessDebitRequest;
use DateTime;

class EnettTest extends TestBase
{
    /**
     * Test ProcessDebitRequestShouldValidateAndSerializeProperly
     *
     * @return void
     */
    public function testProcessDebitRequestShouldValidateAndSerializeProperly()
    {
        $req = new ProcessDebitRequest([
          'transID' => '1234567',
          'primaryRef' => '987654',
          'secondaryRef' => '',
          'passengerName' => 'John Citizen',
          'departureDate' => '2017-10-01',
          'notes' => 'Testing notes',
          'ECN' => '500318',
          'amount' => 10.00,
          'currency' => 'AUD',
          'paymentDate' => date('Y-m-d'),
          'agentID' => '500221',
          'payer' => '500221',
        ]);

        $this->assertEquals($req->transID, '1234567');
        $this->assertEquals($req->primaryRef, '987654');
        $this->assertEquals($req->secondaryRef, '');
        $this->assertEquals($req->passengerName, 'John Citizen');
        $this->assertEquals($req->departureDate, '2017-10-01');
        $this->assertEquals($req->notes, 'Testing notes');
        $this->assertEquals($req->ECN, '500318');
        $this->assertEquals($req->amount, 10.00);
        $this->assertEquals($req->currency, 'AUD');
        $this->assertEquals($req->paymentDate, date('Y-m-d'));
        $this->assertEquals($req->agentID, '500221');
        $this->assertEquals($req->payer, '500221');
        $this->assertEquals($req->validate(), true);
        $this->assertEquals($req->toDataString(), 'transID=1234567&primaryRef=987654&secondaryRef=&passengerName=John+Citizen&departureDate=2017-10-01&notes=Testing+notes&ECN=500318&amount=10.00&currency=AUD&paymentDate=' . date('Y-m-d') . '&agentID=500221&payer=500221');

        $req->amount = 'Test';
        $this->assertEquals($req->validate(), false);
        $this->assertEquals(count($req->getErrors()), 1);
        $this->assertEquals('Please enter a valid transaction amount', end($req->getErrors()));

        $req->currency = 'HKD';
        $this->assertEquals($req->validate(), false);
        $this->assertEquals(count($req->getErrors()), 2);
        $this->assertEquals('Currency is not supported', end($req->getErrors()));

        $req->departureDate = '2017-02-30';
        $this->assertEquals($req->validate(), false);
        $this->assertEquals(count($req->getErrors()), 3);
        $this->assertEquals('Please enter a valid departure date', end($req->getErrors()));

        $req->paymentDate = '01/04/2017';
        $this->assertEquals($req->validate(), false);
        $this->assertEquals(count($req->getErrors()), 4);
        $this->assertEquals('Please enter a valid payment date', end($req->getErrors()));
    }

    /**
     * Test ProcessDebitResponseShouldSerializeProperly
     *
     * @return void
     */
    public function testProcessDebitResponseShouldSerializeProperly()
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<ResponseMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://enett.com/webservices/">
  <successful>true</successful>
  <transactionID>5378097</transactionID>
  <authorisationDateTime>2017-04-29T00:00:00</authorisationDateTime>
</ResponseMessage>
XML;

        $response = new EnettResponse($xml);

        $array = $response->toArray();
        $isArray = is_array($array);
        $this->assertEquals($isArray, true);
        if ($isArray) {
          $this->assertEquals($array['successful'] == 'true', true);
          $this->assertEquals($array['transactionID'] == '5378097', true);
          $this->assertEquals($array['authorisationDateTime'] == '2017-04-29T00:00:00', true);
        }

        $json = $response->toJson();
        $this->assertEquals($json == '{"successful":"true","transactionID":"5378097","authorisationDateTime":"2017-04-29T00:00:00"}', true);
    }

    /**
     * Test API processDebitRequest
     *
     * @return void
     */
    public function testProcessDebitRequestShouldReturnResult()
    {
      $req = new ProcessDebitRequest([
        'transID' => '1234567',
        'primaryRef' => '987654',
        'secondaryRef' => '',
        'passengerName' => 'John Citizen',
        'departureDate' => '2017-10-01',
        'notes' => 'Testing notes',
        'ECN' => '500318',
        'amount' => 10.00,
        'currency' => 'AUD',
        'paymentDate' => date('Y-m-d'),
        'agentID' => '500221',
        'payer' => '500221',
      ]);
      $response = $this->api->processDebitRequest($req);
      $this->assertEquals($response->isSuccess(), true);
      $this->assertEquals($response->isError(), false);
      $this->assertEquals($response->transactionID != '', true);
      $dateTime = new DateTime($response->authorisationDateTime);
      $this->assertEquals($dateTime instanceof DateTime, true);
    }
}
