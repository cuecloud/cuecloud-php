<?php

namespace CueCloud\API;

class Client
{

    /**
    * The base URL to use, eg: live URL, development/local URL, etc.
    * @var string
    */
    private $baseUrl = 'https://cuecloud.com/api/';

    /**
    * The base URL to use, eg: live, development, etc.
    * @var string
    */
    private $apiKey = '';

    /**
    * The base URL to use, eg: live, development, etc.
    * @var string
    */
    private $apiPass = '';


    /**
     * The API version to use
     * @var string
     */
    protected $apiVer = 'v1.0';


    /**
     * Constructor method for the Client Class
     * @param string $apiKey  The API Key provided by CueCLoud
     * @param string $apiPass The API Password, provided by CueCloud
     * @param string $v       The version of the API. Default to 1.0. Optional
     * @param string $baseUrl The base URL to use. Defaults to live. Optional
     */
    public function __construct($apiKey, $apiPass, $v = '', $baseUrl = '')
    {
        // Set the values for the API Key and Password
        $this->apiKey = $apiKey;
        $this->apiPass = $apiPass;

        // If a base URL is passed, overwrite the default one
        if ($baseUrl) {
            $this->baseUrl = $baseUrl;
        }

        // If an API version is passed, overwrite the default one
        if ($v) {
            $this->apiVer = $v;
        }

        // Set the end point on the http client
        Http::setEndPoint($this->baseUrl . $this->apiVer);
    }

    /**
     * Test method to make sure that the user has valid API credentials.
     *
     * @return object Request response, decoded JSON.
     */
    public function validateUser()
    {
        $method = 'GET';
        $resource = '/validate/';
        return Http::request($this, $resource, $method);
    }

    /**
     * Request common keywords for Cues, that are returned within a list.
     * Useful for CueCreation.
     *
     * @return object Request response, decoded JSON.
     */
    public function getKeywords()
    {
        $method = 'GET';
        $resource = '/cues/keywords/';
        return Http::request($this, $resource, $method);
    }

    /**
     * Request the user's current balance, in USD.
     *
     * @return object Request response, decoded JSON.
     */
    public function getBalance()
    {
        $method = 'GET';
        $resource = '/balance/';
        return Http::request($this, $resource, $method);
    }

    /**
     * Given a valid credit card on file in the app, this will deposit a given
     * amount into the user's balance.
     * NOTE: A credit card may only be added within the app, NOT the API.
     *
     * @param float $amoundUSD  The amount of dollars to withdraw
     * @param float $ccLastFour The last 4 digits of their credit card
     *
     * @return object Request response, decoded JSON.
     */
    public function makeDeposit($amountUSD, $ccLastFour)
    {
        $method = 'POST';
        $resource = '/payments/deposit/';
        $data = array(
            'AmountInUSD'               => $amountUSD,
            'CreditCardLastFourDigits'  => $ccLastFour
        );
        return Http::request($this, $resource, $method, $data);
    }

    /**
     * Given a PayPal email, this will deposit the funds immediately into that
     * user's PayPal account.
     * If no amount is specified, it will try and deduct the entire user's
     * balance.
     *
     * @param float $amoundUSD The amount of dollars to withdraw
     *
     * @return object Request response, decoded JSON.
     */
    public function withdrawFunds($amountUSD)
    {
        $method = 'POST';
        $resource = '/payments/withdraw/';
        $data = array('AmountInUSD' => $amountUSD);
        return Http::request($this, $resource, $method, $data);
    }

    /**
     * This will grant a bonus to the user who has completed a particular Cue
     * for us.
     * A reason for the bonus must be specified, though here will be defaulted
     * to "Thanks for your hard work!" if none is provided.
     * Note to self can be provided, which is a strin that can only be viewed by
     * the person who granted the bonus. An example might be: "Bonus paid here
     * on 2014-01-01 to see if it motivates better work from this person."
     *
     * @param string $cueCompletionId The ID for the Cue
     * @param float  $amount          The amount of dollars to grant as bonus
     * @param string $reason          The reason to give the bonus. Optional.
     * @param string $noteToSelf      Note to self. Optional.
     *
     * @return object Request response, decoded JSON.
     */
    public function grantBonus(
        $cueCompletionId,
        $amount,
        $reason = 'Thanks for your hard work!',
        $noteToSelf = ''
    ) {
        $method = 'POST';
        $resource = '/payments/bonus/';
        $data = array(
            'CueCompletionID'   => $cueCompletionId,
            'Amount'            => $amount,
            'Reason'            => $reason,
            'NoteToSelf'        => $noteToSelf
        );
        return Http::request($this, $resource, $method, $data);
    }

    /**
     * Get a list of payments, with some options to filter. If not filter is
     * provided, all payments will be returned.
     * 50 results per page.
     *
     * @param string $paymentType Valid values: Deposits, Withdrawals, Bonuses
     *                            Optional parameter.
     * @param string $paymentId   Payment ID to retrieve. Optional.
     * @param string $noteToSelf  Note to self. Optional.
     * @param string $page        Pagination. Optional.
     *
     * @return object Request response, decoded JSON.
     */
    public function getPayments(
        $paymentType = '',
        $paymentId = '',
        $noteToSelf = '',
        $page = ''
    ) {
        $method = 'GET';
        $resource = '/payments/';
        $data = array(
            'PaymentType'   => $paymentType,
            'PaymentID'     => $paymentId,
            'NoteToSelf'    => $noteToSelf,
            'Page'          => $page
        );
        return Http::request($this, $resource, $method, $data);
    }

    /**
     * Approve a CueCompletion that has been submitted to a user's Cue.
     *
     * @param string $cueCompletionId The ID for the Cue
     *
     * @return object Request response, decoded JSON.
     */
    public function approveCueCompletion($cueCompletionId)
    {
        $method = 'POST';
        $resource = '/completions/approve/';
        $data = array('CueCompletionID' => $cueCompletionId);
        return Http::request($this, $resource, $method, $data);
    }

    /**
     * Decline a CueCompletion that has been submitted to a user's Cue.
     *
     * @param string $cueCompletionId The ID for the Cue
     *
     * @return object Request response, decoded JSON.
     */
    public function declineCueCompletion($cueCompletionId)
    {
        $method = 'POST';
        $resource = '/completions/decline/';
        $data = array('CueCompletionID' => $cueCompletionId);
        return Http::request($this, $resource, $method, $data);
    }

    /**
     * Cancel a Cue that the user has posted, refunding their balance
     *
     * @param string $cueId The ID for the Cue
     *
     * @return object Request response, decoded JSON.
     */
    public function cancelCue($cueId)
    {
        $method = 'POST';
        $resource = '/cues/cancel/';
        $data = array('CueID' => $cueId);
        return Http::request($this, $resource, $method, $data);
    }

    /**
     * Create a new Cue.
     * The only required parameters are the title, amount, and the number of
     * opportunities (which defaults to 1 otherwise).
     * An iframe URL can be specified so the user would fill a custom form on
     * a given URL (usually your site)
     *
     * @param string  $title
     * @param float   $amount
     * @param int     $numOpportunities
     * @param string  $description
     * @param boolean $isAnonymous
     * @param boolean $pushNotificationOnCueCompletion
     * @param boolean $PushNotificationOnCueCompletion
     * @param boolean $disallowAnonymous
     * @param string  $iFrameUrl
     * @param string  $urlNotificationOnCueCompletion
     * @param string  $emailNotificationOnCueCompletion
     * @param int     $lifetimeInMinutes
     * @param int     $timeLimitToCompleteCueInMinutes
     * @param int     $autoApproveCueCompletionInMinute
     * @param string  $noteToSelf
     * @param string  $keywords
     *
     * @return object Request response, decoded JSON.
     */
    public function createCue(
        $title,
        $amount,
        $numOpportunities = 1,
        $description = '',
        $isAnonymous = false,
        $pushNotificationOnCueCompletion = '',
        $disallowAnonymous = '',
        $iFrameUrl = '',
        $urlNotificationOnCueCompletion = '',
        $emailNotificationOnCueCompletion = '',
        $lifetimeInMinutes = '',
        $timeLimitToCompleteCueInMinutes = '',
        $autoApproveCueCompletionInMinute = '',
        $noteToSelf = '',
        $keywords = ''
    ) {
        $method = 'POST';
        $resource = '/cues/create';
        $data = array(
            'Title'                             => $title,
            'Amount'                            => $amount,
            'NumOpportunities'                  => $numOpportunities,
            'Description'                       => $description,
            'IsAnonymous'                       => $isAnonymous,
            'PushNotificationOnCueCompletion'   => $pushNotificationOnCueCompletion,
            'DisallowAnonymousCueCompletions'   => $disallowAnonymous,
            'iFrameURL'                         => $iFrameUrl,
            'URLNotificationOnCueCompletion'    => $urlNotificationOnCueCompletion,
            'EmailNotificationOnCueCompletion'  => $emailNotificationOnCueCompletion,
            'LifetimeInMinutes'                 => $lifetimeInMinutes,
            'TimeLimitToCompleteCueInMinutes'   => $timeLimitToCompleteCueInMinutes,
            'AutoApproveCueCompletionAfterThisManyMinutes' => $autoApproveCueCompletionInMinute,
            'NoteToSelf'                        => $noteToSelf,
            'Keywords'                          => $keywords,
        );
        return Http::request($this, $resource, $method, $data);
    }

    /**
     * Get a list of all the Cues the use has created.
     * Some filters are available as parameters.
     *
     * @param string  $cueId                    Retrieve a specific Cue by ID
     * @param string  $groupId                  Filter by group ID
     * @param string  $noteToSelf               Note to self
     * @param boolean $hasPendingCueCompletions Retrieve cues with pending Cue
     *                                          completions
     * @param string  $status                   Filter by status
     *                                          Active|Complete|Canceled|Expired
     * @param int     $page                     Pagination
     *
     * @return object Request response, decoded JSON.
     */
    public function getCues(
        $cueId = '',
        $groupId = '',
        $noteToSelf = '',
        $hasPendingCueCompletions = '',
        $status = '',
        $page = ''
    ) {
        $method = 'GET';
        $resource = '/cues/';
        $data = array(
            'CueID'                     => $cueId,
            'GroupID'                   => $groupId,
            'NoteToSelf'                => $noteToSelf,
            'HasPendingCueCompletions'  => $hasPendingCueCompletions,
            'Status'                    => $status,
            'Page'                      => $page,
        );
        return Http::request($this, $resource, $method, $data);
    }

    /**
     * Try and check-in or check-out a Cue depending on whether the Cue is
     * already checked out by that user.
     *
     * @param string $cueId The ID for the Cue
     *
     * @return object Request response, decoded JSON.
     */
    public function assignCue($cueId)
    {
        $method = 'POST';
        $resource = '/cues/assign/';
        $data = array('CueID' => $cueId);
        return Http::request($this, $resource, $method, $data);
    }

    /**
     * Get CueCompletions for a particular Cue, or filter by CueCompletion, or
     * status.
     *
     * @param string $cueId           The ID for the Cue
     * @param string $cueCompletionId The ID for the CueCompletion
     * @param string $status          Pending|Accepted|Declined
     * @param int    $page            Pagination
     *
     * @return object Request response, decoded JSON.
     */
    public function getCueCompletion(
        $cueId = '',
        $cueCompletionId = '',
        $status = '',
        $page = ''
    ) {
        $method = 'GET';
        $resource = '/completions/';
        $data = array(
            'CueID'             => $cueId,
            'CueCompletionID'   => $cueCompletionId,
            'Status'            => $status,
            'Page'              => $page
        );
        return Http::request($this, $resource, $method, $data);
    }

    /**
     * Submit the CueCompletion data, though in production the method will block
     * any requests without an HTTP_REFERER.
     *
     * @param string  $assignmentId   The assigment ID
     * @param string  $answerText     The answer text
     * @param string  $videoUrl       The video URL
     * @param string  $videoThumbnail The video thumbnail URL
     * @param string  $imageUrl       The image URL
     * @param boolean $isAnonymous    Is the CueCompletion anonymous?
     *
     * @return object Request response, decoded JSON.
     */
    public function submitCueCompletion(
        $assignmentId,
        $answerText = '',
        $videoUrl = '',
        $videoThumbnail = '',
        $imageUrl = '',
        $isAnonymous = false
    ) {
        $method = 'POST';
        $resource = '/cues/complete/';
        $data = array(
            'AssignmentID'      => $assignmentId,
            'AnswerText'        => $answerText,
            'VideoURL'          => $videoUrl,
            'VideoThumbnailURL' => $videoThumbnail,
            'ImageURL'          => $imageUrl,
            'IsAnonymous'       => $isAnonymous
        );
        return Http::request($this, $resource, $method, $data);
    }


    /**
     * Get the private API Key attribute
     * @return string The API Key
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Get the private API Pass attribute
     * @return string The API Key
     */
    public function getApiPass()
    {
        return $this->apiPass;
    }
}
