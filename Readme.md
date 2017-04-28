# Enett:
A simple Laravel 5 Enett payment gateway library.


# Setps for installation:
1. Use following command in your terminal to install this library. (Currently the library is in development mode):

	composer require dploy/enett dev-master

2. Update the poviders in config/app.php

		'providers' => [
	        // ...
	        Dploy\Enett\EnettServiceProvider::class,
	    ]

3. Update the aliases in config/app.php

	    'aliases' => [
	        // ...
	        'Enett' => Dploy\Enett\Facade\Enett::class,
	    ]

4. Add following line in composer.json in your project root only

		 "autoload": {
		        "psr-4": {
			        ......
			        ......
		            "Dploy\\Enett\\": "src/"
		        }
		    },

5. Use composer command in your terminal

		composer dump-autoload

6. To use your own settings, publish config.

		$ php artisan vendor:publish

This is going to add config/enett.php file

NOTE: Make sure you have curl install in your system.


# Examples:
Please find the example below:

 		// add name space in your controller
 		use Enett
		use Dploy\Enett\Models\ProcessDebitRequest;

 		// In controller action, add the following code

		// Create request object, this is going to hold all your parameters
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

		// Now we are ready to make our call, this is going to make your direct payment in eNett gateway
		$result = Enet::processDebitRequest($req);

		// Here you can check the returned response
		var_dump($result);

Results:

If all parameters are correct then API will return an XML structure following eNett's specifications.

If case of error or missing parameters
Errors are thrown using classes
- EnettException

For example:

		throw new EnettException($request);


# Enett server-to-server payment API note:
NOTE: You should be fully PCI compliant if you wish to perform an initial payment request server-to-server (as it requires that you collect the card data). If you are not fully PCI compliant, you can use Enett.js to collect the payment data securely.

# Methods
- processDebitRequest

# Path to Config file:
		/src/Config/enett.php

# Environment variables used:

		ENETT_ENVIRONMENT = test|live
		ENETT_INTEGRATOR = replace with your integrator ID
		ENETT_KEY = replace with your eNett key
		ENETT_VERSION=1.0
		ENETT_SOURCE = replace with your website URL
