<?php

namespace Dploy\Enett\Models;

class EnettRequest {

  protected $errors;
  protected $data = [];
	protected $validation = [];
  protected $excludeFields = [];

	public function __construct($params = [])
	{
    foreach($params as $k => $v) {
      switch($k) {
        case 'amount':
          $this->$k = preg_replace('@[^0-9\.]@', '', number_format((float)$v, 2));
          break;
        default:
  			   $this->$k = $v;
      }
		}
	}

  public function __get($name)
	{
  	return isset($this->data[$name]) ? $this->data[$name] : null;
  }

  public function __set($name, $value)
	{
    $this->data[$name] = $value;
  }

	public function get($name)
	{
		if (is_null($name) || $name == '') return null;
		$parts = explode('.', $name);
		$value = $this->data;
		while($key = array_shift($parts)) {
			if (isset($value[$key])) {
				$value = $value[$key];
			} else {
				$value = null;
				break;
			}
		}
		return $value;
	}

	public function set($name, $val)
	{
		if (is_null($name) || $name == '') return null;
		$parts = explode('.', $name);
		$value = $this->data;
    $ref = $first = null;
		while(count($parts) > 0 && $key = array_shift($parts)) {
      if (is_null($first)) {
        $first = $key;
      }
			if (isset($value[$key])) {
        $ref = $value;
				$value = $value[$key];
			} else {
        break;
      }
		}
    if ($ref) {
      $ref[$key] = $val;
    }
    $this->data[$first] = $ref;
	}

	public function toDataString()
	{
		$vars = $this->buildDataArray($this->data);
		return http_build_query($vars);
	}

	public function validate()
	{
		$this->errors = [];
		foreach($this->validation as $key) {
      $value = $this->get($key);
			if (!Validation::validate($key, $value)) {
				$this->errors[] = Validation::get($key);
			}
		}
		return !count($this->errors);
	}

  public function getErrors()
  {
    return $this->errors;
  }

	protected function buildDataArray($data)
	{
		$vars = [];
		foreach($data as $k => $v) {
      if (!in_array($k, $this->excludeFields)) {
  			if (is_array($v)) {
  				$arr = $this->buildDataArray($v);
  				foreach($arr as $key => $value) {
  					$vars[$k . '.' . $key] = $value;
  				}
  			} else {
  				$vars[$k] = $v;
  			}
      }
		}
		return $vars;
	}
}
