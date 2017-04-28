<?php

namespace Dploy\Enett\Tests {

use PHPUnit_Framework_TestCase;
use Dploy\Enett\Enett;

class TestBase extends PHPUnit_Framework_TestCase
{
    protected $api;

    public function setUp()
    {
      $config = require realpath(dirname(__FILE__) . '/../src/Config/enett.php');
      $this->api = new Enett($config);
    }

    public function tearDown()
    {

    }
}

}

namespace {
  if (!function_exists('env')) {
    function env($var, $default = '') {
      return isset($_ENV[$var]) ? $_END[$var] : $default;
    }
  }
  if (!function_exists('dd')) {
    function dd() {
      call_user_func_array('var_dump', func_get_args());
      exit;
    }
  }
  if (! function_exists('camel_case')) {
    /**
     * Convert a value to camel case.
     *
     * @param  string  $value
     * @return string
     */
    function camel_case($value)
    {
      return lcfirst(ucwords(str_replace(['-', '_', '.'], ' ', $value)));
    }
  }
}
