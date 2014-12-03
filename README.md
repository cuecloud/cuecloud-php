# CueCloud PHP API Client Library #

## Installation

The CueCloud PHP API client can be installed using [Composer](https://getcomposer.org/).

### Composer

Inside of composer.json specify the following:

```json
{
    "require": {
        "cuecloud/cuecloud-php": "dev-master"
    }
}
```
## Configuration

Configuration is done through the constructor of CueCloud\API\Client.
This is mandatory and if not passed none of the API methods will work.

```php
use CueCloud\API\Client as CueCloudAPI;

$apiKey = 'your_API_key';
$apiPass = 'your_secret_password';

$client = new CueCloud($apiKey, $apiPass);
```
Additional parameters could be passed: the version of the API, and the base URL as well (although this parameter is only useful for CueCloud developers).

## Running Tests
A collection of integration tests is included. To execute them use phpunit, which should be already installed via composer because it's a development dependency of the CueCloud PHP API client:

```bash
phpunit ./tests/
```
This will use the settings included on phpunit.xml.

Alternatively (useful only for CueCloud developers) the integration tests could be ran against their local development server using the settings on phpunit.local.xml, executing:
```bash
phpunit -c phpunit.local.xml ./tests/
```


## Copyright and license

Copyright 2014 CueCloud. Licensed under the MIT License.
