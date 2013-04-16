Basecamp API (new) PHP Library
================

This is a pure PHP library for the (new) [37Signals Basecamp API](https://github.com/37signals/bcx-api)

## Requirements
In order to use the oAuth authentication method, you must first install the [PECL oAuth library](http://php.net/oauth)

    $ pecl install oAuth

## Authentication

You can authenticate to Basecamp via the library in one of 3 ways:

1.  Using basic HTTP authentication, which only requires your username and password
2.  Using oAuth authentication with a prefetched token (oAuth happens outside of the library).
3.  Using oAuth authentication, using the library to handle the full oAuth process.
  
  Full oAuth authentication requires the [PECL oAuth library](http://php.net/oauth). Either oAuth authentication requires you to create an application with Basecamp at [integrate.37signals.com](https://integrate.37signals.com). You can read the full Basecamp oAuth authentication instructions at the [37Signals Basecamp API - Authentication Documentation](https://github.com/37signals/api/blob/master/sections/authentication.md#oauth-2)

You create the client with an application name, and then set your authentication method.

### HTTP Basic Authorization with username and password

```php
<?php
$basecamp = new Basecamp('MyBasecampIntegration');
$basecamp->setServerAuthentication('username','password');
?>
```

### oAuth Authorization

This library offers methods to both request and receive the authorization token, as well as to authenticate using the oAuth token.

#### oAuth Authentication with pre-fetched token
```php
<?php
$basecamp = new Basecamp('MyBasecampIntegration');
$basecamp->setOAuthAuthenticationToken('TOKEN');
?>
```

#### oAuth Authentication from start to finish
In order to have the library handle oAuth authentication from start to finish, use the setOAuthAuthentication method. This method sets up the oAuth variables and a _SESSION variable to store the fetched token. In this example we use one file as the login handler, and then another as the communication page. For example:

**login.php**
```php
<?php
$basecamp = new Basecamp('MyBasecampIntegration');
//Set up the oAuth Variables
$basecamp->setOAuthAuthentication($_consumer_key,$_consumer_secret,'login.php');

//If we are already authenticated, we don't need to authenticate again, so go right to the communication page
if ( $basecamp->authenticated ) {
  header("Location:communication.php");
} else {
//If we received a code back from the system, set it and redirect to the communication page
	if ( $_REQUEST['code'] ) {
		$basecamp->fetchToken($_REQUEST['code']);
		header("Location:communication.php");
		exit();
	} else {
//Redirect the user to authenticate via Basecamp dialog  
		$basecamp->redirectToDialog();	
	}	
}
?>
```
**communication.php**
```php
<?php
$basecamp = new Basecamp('MyBasecampIntegration');
//Use the same oAuth settings as on login.php
$basecamp->setOAuthAuthentication($_consumer_key,$_consumer_secret,'login.php');

//return a list of accounts my user has access to
$basecamp->getAccounts();
?>
```

## Usage
Once you have selected your authentication method, and successfully authenticated with Basecamp you can begin to interact with the Basecamp API. The library supports all current (as of 4/2013) Basecamp API requests. For most requests, you will need to set the account you wish to request against. You can get a list of accounts using the `getAccounts` method.

```php
<?php
$basecamp = new Basecamp('MyBasecampIntegration');
$basecamp->setServerAuthentication('username','password');
$basecamp->getAccounts();
?>
```

This willl return something like: 
```php
Array
(
    [0] => stdClass Object
        (
            [name] => Arkitech
            [href] => https://basecamp.com/88888888/api/v1
            [id] => 88888888
            [product] => bcx
        )

    [1] => stdClass Object
        (
            [name] => Veidt, Inc
            [href] => https://basecamp.com/77777777/api/v1
            [id] => 77777777
            [product] => bcx
        )

    [2] => stdClass Object
        (
            [name] => Acme Shipping Co.
            [href] => https://acme4444444.campfirenow.co
            [id] => 44444444
            [product] => campfire
        )

)
```
Using the value from the `href` attribute of the desired account you can set the account to make requests against using the `setAccount` method.

```php
<?php
$basecamp = new Basecamp('MyBasecampIntegration');
$basecamp->setServerAuthentication('username','password');
$basecamp->setAccount('https://basecamp.com/88888888/api/v1');
$basecamp->getProjects();
?>
```

