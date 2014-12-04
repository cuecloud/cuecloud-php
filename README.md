# CueCloud PHP API Client Library #

## Installation

The CueCloud PHP API client can be installed using [Composer](https://getcomposer.org/). You shouldn't have any problem installing it following [these steps](https://getcomposer.org/doc/00-intro.md#installation-nix).

### Composer

You'll need to create a composer file for your project, you can do it editing it yourself or generate it answering the questions that composer would ask you after running:

```bash
composer init
```

You need to set the CueCloud PHP API Client as a required package for your project, using the package identifier (`cuecloud/cuecloud-php`).

Your composer.json should look something like this:

The result should be something like (pay special attention to the `require` item):
```json
{
    "name": "test/my-test",
    "description": "This is a test",
    "license": "MIT License",
    "authors": [
        {
            "name": "John Doe",
            "email": "john@doe.com"
        }
    ],
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

## Usage
Once you added the library as a requirement in composer, and set your API key and password when instantiating the CueCloud client, you are ready to call any of the methods provided:

### validateUser()
Test method to make sure that the user has valid API credentials.
### getKeywords()
Request common keywords for Cues, that are returned within a list.
### getBalance()
Request the user's current balance, in USD.
### makeDeposit($amountUSD, $ccLastFour)
Given a valid credit card on file in the app, this will deposit a given amount into the user's balance.
### withdrawFunds($amountUSD)
Given a PayPal email, this will deposit the funds immediately into that user's PayPal account.
### grantBonus($cueCompletionId, $amount, $reason, $noteToSelf)
This will grant a bonus to the user who has completed a particular Cue for us.
### getPayments($paymentType, $paymentId, $noteToSelf, $page)
Get a list of payments, with some options to filter. If not filter is provided, all payments will be returned.
### approveCueCompletion($cueCompletionId)
Approve a CueCompletion that has been submitted to a user's Cue.
### declineCueCompletion($cueCompletionId)
Decline a CueCompletion that has been submitted to a user's Cue.
### cancelCue($cueId)
Cancel a Cue that the user has posted, refunding their balance
### createCue($title, $amount, $numOpportunities, $description, $isAnonymous, $pushNotificationOnCueCompletion, $disallowAnonymous, $iFrameUrl, $urlNotificationOnCueCompletion, $emailNotificationOnCueCompletion, $lifetimeInMinutes, $timeLimitToCompleteCueInMinutes, $autoApproveCueCompletionInMinute, $noteToSelf, $keywords)
Create a new Cue.
The only required parameters are the title, amount, and the number of opportunities (which defaults to 1 otherwise).
An iframe URL can be specified so the user would fill a custom form on a given URL (usually your site)
### getCues($cueId, $groupId, $noteToSelf, $hasPendingCueCompletions, $status, $page)
Get a list of all the Cues the use has created. Some filters are available as parameters.
### assignCue($cueId)
Try and check-in or check-out a Cue depending on whether the Cue is already checked out by that user.
### getCueCompletion($cueId, $cueCompletionId, $status, $page)
Get CueCompletions for a particular Cue, or filter by CueCompletion, or status.
### submitCueCompletion($assignmentId, $answerText, $videoUrl, $videoThumbnail, $imageUrl, $isAnonymous)
Submit the CueCompletion data, though in production the method will block any requests without an HTTP_REFERER.


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
