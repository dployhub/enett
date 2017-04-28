<?php

namespace Dploy\Enett\Models;

class ProcessDebitRequest extends EnettRequest {

  protected $validation = [
    'amount', 'currency', 'departureDate', 'paymentDate',
  ];

}
