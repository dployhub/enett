<?php
namespace Dploy\Enett;

use Monolog\Logger;
use Dploy\Enett\Exceptions\EnettException;
use Dploy\Enett\Models\EnettResponse;
use Dploy\Enett\Models\EnettRequest;
use Dploy\Enett\Models\ProcessDebitRequest;

class Enett {

	const SERVICE_CREDIT = 'credit';
	const SERVICE_DEBIT = 'debit';
	protected $config;
	protected $hosts;
	protected $services;
	protected $env;
	protected $version;
	protected $integrator;
	protected $source;
	protected $key;
	protected $ssl_verifier;
	protected $log;

	public function __construct($config = [], Logger $log = null)
	{
		$this->config = $config;
		$this->hosts = [
			'test' => 'https://enett-demo.com',
			'live' => 'https://enett.com',
		];
		$this->services = [
			static::SERVICE_CREDIT => '/CCService/ccservice.asmx',
			static::SERVICE_DEBIT => '/DebitService/Debitservice.asmx',
		];
		$this->integrator = $config['integrator'];
		$this->source = $config['source'];
		$this->version = $config['version'];
		$this->key = $config['key'];
		$this->env = $config['environment'];
		$this->ssl_verifier = $this->env == 'live';
		$this->log = $log;
	}

	public function getHost(){
    return isset($this->hosts[$this->env]) ? $this->hosts[$this->env] : '';
	}

	public function getService($service)
	{
		if (!in_array($service, [static::SERVICE_CREDIT, static::SERVICE_DEBIT])) {
			throw new EnettException('eNett service not supported.');
		}
		return $this->services[$service];
	}

  public function getUrl($service, $url){
    return $this->getHost() . $this->getService($service) . '/' . ltrim($url, '/');
  }

	public function getIntegrator()
	{
		return $this->integrator;
	}

	public function getKey()
	{
		return $this->key;
	}

	public function getSource()
	{
		return $this->source;
	}

	public function getVersion()
	{
		return $this->version;
	}

	public function getEnvironment(){
		return $this->env;
	}

	public function setEnvironment($environment){
		return $this->env = $environment; //live or
	}

  /**
	 * Method to make curl requests using post & get methods
	 * Requires:
	 * @param string url
	 * @param string data
	 * @param string requestMethod
	 * @param string requestType
	 */
  public function curl_request($service, $url, $requestMethod = 'POST', $data = null){
		$url = $this->getUrl($service, $url);
		$requestMethod = strtoupper($requestMethod);

		$this->log('info', $requestMethod . ': ' . $url);

  	$ch = curl_init();
		if ($requestMethod == 'POST') {
			if ($data instanceof EnettRequest) {
				$data = $data->toDataString();
			}

			$this->log('info', $this->sanitize($data));

			$data = http_build_query([
				'integrator' => $this->getIntegrator(),
				'version' => $this->getVersion(),
        'source' => $this->getSource(),
				'integratorKey' => $this->getKey(),
			]) . '&' . $data;

			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		} elseif (in_array($requestMethod, ['DELETE'])) {
    	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestMethod);
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifier);// this should be set to true in production
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		if(curl_errno($ch)) {
			$error = new EnettException(sprintf('Could not connect to Enett: %s', curl_error($ch)));
			curl_close($ch);
			throw $error;
		}
		curl_close($ch);
		return $response;

    }
	//----------------- Direct Debit ---------------------------
  /**
	 * Charge a customer via direct debit
	 * Requires:
	 * @param string userId
	 * @param string password
	 * @param string entityId
	 * @param string paymentBrand
	 * @param int cardNumber
	 * @param string cardHolder
	 * @param int cardExpiryMonth
	 * @param int cardExpiryYear
	 * @param int cardcvv
   */
  public function processDebitRequest(ProcessDebitRequest $request)
	{
		$this->validateRequest($request);
		$response = $this->curl_request(static::SERVICE_DEBIT, 'Process_Direct_Debit', 'POST', $request);
		return $this->response($response);
	}

  /* ---- Protected Methods -- */
	protected function validateRequest(EnettRequest $request)
	{
		if (!$request->validate()) {
			throw new EnettException($request);
		}
	}

	protected function response($responseJson)
	{
		$response = new EnettResponse($responseJson);
		if ($response->isError()) {
			$this->log('error', $responseJson);
			throw new EnettException($response);
		}
		return $response;
	}

	protected function log($severity, $msg)
	{
		if ($this->log) $this->log->$severity($msg);
	}

  /**
   * As per PCI compliance, we do not want to log any credit card numbers
   */
  protected function sanitize($data, $mask = 'X')
  {
    $qs = explode('&', $data);
    $query = [];
    foreach($qs as $q) {
      list($k, $v) = explode('=', $q);
      $query[$k] = $v;
    }

    $ccFields = ['card.number'];
    foreach($ccFields as $field) {
      if (isset($query[$field])) {
        $query[$field] = $this->maskCc($query[$field]);
      }
    }
    $parts = [];
    foreach($query as $k => $v) {
      $parts[] = implode('=', [$k, $v]);
    }
    return implode('&', $parts);
  }

  protected function maskCc($cc, $mask = 'X')
  {
    return substr($cc, 0, 4) . str_repeat($mask, strlen($cc) - 8) . substr($cc, -4);
  }
}
