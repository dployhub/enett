<?php namespace Dploy\Enett\Models;

use Exception;

class EnettResponse {

	/**
	*  @var string original response data
	*/
	protected $responseData;

	/**
	*  @var string JSON decoded response
	*/
	protected $response;

	/**
	*  @var object result outcome for the API call
	*/
	protected $successful;

	/**
	*  @var string error message in case of errors
	*/
	protected $errorMessage;

	public function __construct($responseData){
		$this->responseData = $responseData;
		try {
			$this->response = simplexml_load_string($responseData);
			if (is_null($this->response) || !isset($this->response->successful)) {
				throw new Exception('Invalid response from eNett');
			}
			$this->successful = (string)$this->response->successful === 'true';
			if (!$this->successful) {
				$this->errorMessage = (string)$this->response->errorMessage;
			}
		}	catch(Exception $e) {
			$this->successful = false;
			$this->errorMessage = ($responseData != '') ? $responseData : 'Error connecting to Enett server';
		}
	}

	public function getResponse()
	{
		return $this->response;
	}

	public function getResponseData()
	{
		return $this->responseData;
	}

	public function getMessage()
	{
		return $this->errorMessage;
	}

	public function isSuccess()
	{
		return $this->successful;
	}

	public function isError()
	{
		return !$this->successful;
	}

	public function toJson()
	{
		return $this->response ? json_decode($this->response) : '';
	}

	public function toArray()
	{
		return $this->response ? json_decode($this->responseData, true) : [];
	}

	public function __get($value)
	{
		return ($this->response && isset($this->response->$value)) ? $this->response->$value : null;
	}

}
