<?php

namespace Dploy\Enett\Models;

class Validation {

  protected static $errors = [
    'departureDate' => 'Please enter a valid departure date',
    'paymentDate' => 'Please enter a valid payment date',
    'paymentBrand' => 'Invalid payment brand',
    'amount' => 'Please enter a valid transaction amount',
    'currency' => 'Currency is not supported',
    'card.number' => 'Please enter a valid credit card number',
    'card.expiryMonth' => 'Please enter a valid credit card expiry month',
    'card.expiryYear' => 'Please enter a valid credit card expiry year',
    'card.cvv' => 'Please enter a valid cvv',
  ];

  protected static $validPaymentBrands = ['VISA','MASTER','AMEX'];
  protected static $validCurrencies = ['AUD'];

  public static function get($key)
  {
    return isset(self::$errors[$key]) ? self::$errors[$key] : 'Please enter ' . $key;
  }

  public static function validate($key, $value)
  {
    $func = self::getValidateFuncName($key);
    if (is_null($value) || $value == '') return false;
    if (method_exists(self::class, $func)) {
      return self::$func($value);
    }
    return true;
  }

  protected static function getValidateFuncName($key)
  {
    return 'validate' . str_replace(' ', '', ucfirst(camel_case(str_replace('.', ' ', $key))));
  }

  protected static function validatePaymentBrand($value)
  {
    return in_array($value, self::$validPaymentBrands);
  }

  protected static function validateAmount($value)
  {
    return ((float)$value != 0);
  }

  protected static function validateCurrency($value)
  {
    return in_array($value, self::$validCurrencies);
  }

  protected static function validateCardCvv($value)
  {
    $len = strlen($value);
    return (preg_replace('/[^0-9]/', '', $value) == (string)$value && $len > 2 && $len < 5);
  }

  protected static function validateCardNumber($value)
  {
    $value = preg_replace('/[^0-9]/', '', $value);
    return (string)(int)$value == (string)$value;
  }

  protected static function validateCardExpiryMonth($value)
  {
    $value = (int)ltrim($value, '0');
    return ($value > 0 && $value < 13);
  }

  protected static function validateCardExpiryYear($value)
  {
    $value = (int)$value;
    if (strlen($value) == 2) $value += 2000;
    return $value >= date('Y');
  }

  protected static function validatePaymentDate($value)
  {
    return static::isValidDate($value);
  }

  protected static function validateDepartureDate($value)
  {
    return static::isValidDate($value);
  }

  protected static function isValidDate($value)
  {
    $parts = explode('-', $value);
    if (count($parts) != 3) return false;
    if (checkdate($parts[1], $parts[2], $parts[0]) === FALSE) return false;
    return true;
  }
}
