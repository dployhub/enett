<?php namespace Dploy\Enett\Exceptions;

use Exception;

class EnettException extends Exception
{
  protected $data;
  protected $errors = [];

	public function __construct($data)
  {
    $this->data = $data;

    if (is_a($data, 'Dploy\Enett\Models\EnettRequest')) {
      $this->errors = $data->getErrors();
    } elseif (is_a($data, 'Dploy\Enett\Models\EnettResponse')) {
      $this->errors = [$data->getMessage()];
    } elseif (is_string($data)) {
      $this->errors = [$data];
    } else {
      $this->errors = (array)$data;
    }
    parent::__construct($this->getErrors("\n"));
  }

  public function getData()
  {
    return $this->data;
  }

  public function getErrors($sep = null)
  {
    return is_null($sep) ? $this->errors : implode($sep, $this->errors);
  }
}
