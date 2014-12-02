<?php

namespace CueCloud\API\Tests;

use CueCloud\API\Client as Client;

/**
 * Integration tests against the CueCloud API
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{

    private $client;

    public function __construct()
    {
        $this->client = new Client(
            $GLOBALS['API_KEY'],
            $GLOBALS['API_PASS'],
            $GLOBALS['API_VERSION'],
            $GLOBALS['API_URL']
        );
    }

    /**
     * Test that the provided API/login credentials are valid
     * The response will include a 200 response code and a welcome message
     */
    public function testValidateUser()
    {
        $res = $this->client->validateUser();
        $this->assertEquals($res->StatusCode, 200);
        // This is not a mistake, the API returns the welcome message as "Error"
        $this->assertEquals($res->Error, 'Welcome, David Litwin!');
    }

    /**
     * Get the keywords and confirm that 'facebook' is one of the keyords
     * returned
     */
    public function testGetKeywords()
    {
        $res = $this->client->getKeywords();
        $this->assertEquals($res->StatusCode, 200);
        $this->assertContains('facebook', $res->Data->Keywords);
    }

    /**
     * Make a deposit (add funds to a user account) and check that the new
     * balance is exactly the sum of the old one plus the deposit made.
     */
    public function testMakeDeposit()
    {
        $balance = $this->client->getBalance();
        $prevBalance = $balance->Data->Balance;

        $res = $this->client->makeDeposit(
            $GLOBALS['PAY_AMOUNT'],
            $GLOBALS['CC_LAST_FOUR']
        );

        $balance = $this->client->getBalance();
        $newBalance = $balance->Data->Balance;

        $this->assertEquals($GLOBALS['PAY_AMOUNT'], $newBalance-$prevBalance);
    }

    /**
     * Withdraw funds (substract from user account) and check that the new
     * balance is exactly the result of substracting the old one and the
     * amount withdrawn
     */
    public function testWithdrawFunds()
    {
        $balance = $this->client->getBalance();
        $prevBalance = $balance->Data->Balance;

        $res = $this->client->withdrawFunds($GLOBALS['PAY_AMOUNT']);

        $balance = $this->client->getBalance();
        $newBalance = $balance->Data->Balance;

        $this->assertEquals($GLOBALS['PAY_AMOUNT'], $prevBalance-$newBalance);

    }

    /**
     * Create a new Cue and check that:
     *  1) The user now has one more cue to fetch
     *  2) The user balance is now decreased accordingly, using the cue amount,
     *     the number of opportunities and the CueCloud fee for the calculation
     */
    public function testCreateCue()
    {
        // Get the current number of cues
        $cues = $this->client->getCues();
        $prevNumCues = $cues->Data->NumTotalResults;
        // Get the current balance
        $balance = $this->client->getBalance();
        $prevBalance = $balance->Data->Balance;
        // Create a new cue
        $newCue = $this->client->createCue(
            $title = 'New Cue',
            $amount = $GLOBALS['PAY_AMOUNT'],
            $numOpportunities = $GLOBALS['NUM_OPS']
        );
        // Get the current number of cues
        $cues = $this->client->getCues();
        $newNumCues = $cues->Data->NumTotalResults;
        // Check that the number of cues increased
        $this->assertGreaterThan($prevNumCues, $newNumCues);

        $balance = $this->client->getBalance();
        $newBalance = $balance->Data->Balance;
        // Get the new balance, with 2 decimals
        $balanceDiff = number_format($prevBalance - $newBalance, 2);
        // Calculate the theoretical balance difference, with 2 decimals
        $diffShouldBe = number_format(
            (float)$GLOBALS['PAY_AMOUNT'] * $GLOBALS['NUM_OPS'] * (1 + $GLOBALS['CUECLOUD_FEE']),
            2
        );
        // Compare the balance difference with its theoretical value
        $this->assertEquals($balanceDiff, $diffShouldBe);
    }

    /**
     * Submit a Cue Completion and check that the number of new cue completions
     * for that cue is greater than it was previously
     */
    public function testSubmitCueCompletion()
    {
        // Get the list of cues, and get the ID of the last one
        $cues = $this->client->getCues();
        $lastCueId = $cues->Data->Cues[0]->ID;

        // Get the current list of completions, to compare later
        $completions = $this->client->getCueCompletion($lastCueId);
        $numCompletions = $completions->Data->NumTotalResults;

        // Assign the last cue
        $assignCue = $this->client->assignCue($lastCueId);
        $assignmentId = $assignCue->Data->AssignmentID;

        // Submit a cue completion
        $completion = $this->client->submitCueCompletion(
            $assignmentId,
            $answerText = 'My Answer'
        );
        $completionId = $completion->Data->CueCompletionID;

        // Get the updated list of completions
        $completions = $this->client->getCueCompletion($lastCueId);
        $newNumCompletions = $completions->Data->NumTotalResults;

        $this->assertEquals($newNumCompletions, $numCompletions+1);

    }

    /**
     * Grant a bonus and check that the number of payments that the user has is
     * greater than the number of payments that they had prevously
     */
    public function testGrantBonus()
    {
        $payments = $this->client->getPayments();
        $prevNumPayments = $payments->Data->NumTotalResults;

        // Get the list of cues, with pending completion
        $cues = $this->client->getCues(
            $cueId = '',
            $groupId = '',
            $noteToSelf = '',
            $hasPendingCueCompletions = true
        );
        $lastCueId = $cues->Data->Cues[0]->ID;

        // Get the current list of completions, to compare later
        $completions = $this->client->getCueCompletion($lastCueId);
        $lastCueCompletionId = $completions->Data->CueCompletions[0]->ID;

        // Now grant bonus
        $grant = $this->client->grantBonus(
            $lastCueCompletionId,
            $amount = $GLOBALS['PAY_AMOUNT'],
            $reason = 'Nice work'
        );

        $payments = $this->client->getPayments();
        $newNumPayments = $payments->Data->NumTotalResults;
        // Check that there is one more payment
        $this->assertEquals($newNumPayments, $prevNumPayments+1);

        // And check some of its details
        $lastPayment = $payments->Data->Payments[0];
        $this->assertEquals($lastPayment->Reason, 'Nice work');
        $this->assertEquals($lastPayment->Amount, $GLOBALS['PAY_AMOUNT']);
        $this->assertEquals($lastPayment->PaymentType, 'Bonus');

    }

    /**
     * Decline a Cue Completion and check that the number of pending Cue
     * Completions is now reduced by one, compared to the original number
     */
    public function testDeclineCueCompletion()
    {

        // Submit a new cue completion
        // Get the list of cues, and get the ID of the last one
        $cues = $this->client->getCues();
        $lastCueId = $cues->Data->Cues[0]->ID;

        // Assign the last cue
        $assignCue = $this->client->assignCue($lastCueId);
        $assignmentId = $assignCue->Data->AssignmentID;

        // Submit a cue completion
        $completion = $this->client->submitCueCompletion(
            $assignmentId,
            $answerText = 'My Answer'
        );
        $completionId = $completion->Data->CueCompletionID;


        // Get the list of cues, and get the ID of the last one
        $cues = $this->client->getCues();
        $lastCueId = $cues->Data->Cues[0]->ID;

        $cues = $this->client->getCues($lastCueId);
        $prevNumPendingCueCompletions = $cues->Data->Cues[0]->NumberOfCueCompletionsPendingReview;

        // Get the list of cues, with pending completion
        $cues = $this->client->getCueCompletion(
            $lastCueId,
            $cueCompletionId  = '',
            $status           = 'Pending'
        );
        $pendingCueCompletionIdToApprove = $cues->Data->CueCompletions[0]->ID;
        $this->client->declineCueCompletion($pendingCueCompletionIdToApprove);


        $cues = $this->client->getCues($lastCueId);
        $newNumPendingCueCompletions = $cues->Data->Cues[0]->NumberOfCueCompletionsPendingReview;

        $this->assertEquals($prevNumPendingCueCompletions, $newNumPendingCueCompletions+1);
    }

    /**
    * Approve a Cue Completion and check that the number of pending Cue
    * Completions is now increased by one, compared to the original number
    */
    public function testApproveCueCompletion()
    {
        // Get the list of cues, and get the ID of the last one
        $cues = $this->client->getCues();
        $lastCueId = $cues->Data->Cues[0]->ID;

        $cues = $this->client->getCues($lastCueId);
        $prevNumApprovedCueCompletions = $cues->Data->Cues[0]->NumberOfCueCompletionsApproved;

        // Get the list of cues, with pending completion
        $cues = $this->client->getCueCompletion(
            $lastCueId,
            $cueCompletionId  = '',
            $status = 'Canceled'
        );
        $cueCompletionIdToApprove = $cues->Data->CueCompletions[0]->ID;
        $this->client->approveCueCompletion($cueCompletionIdToApprove);

        $cues = $this->client->getCues($lastCueId);
        $newNumApprovedCueCompletions = $cues->Data->Cues[0]->NumberOfCueCompletionsApproved;

        $this->assertEquals($prevNumApprovedCueCompletions+1, $newNumApprovedCueCompletions);

    }

    /**
     * Cancel a Cue and check that the number of Active Cues the user has is
     * now one less than it previously was
     */
    public function testCancelCue()
    {
        // Get the list of active cues
        $cues = $this->client->getCues(
            $cueId                      = '',
            $groupId                    = '',
            $noteToSelf                 = '',
            $hasPendingCueCompletions   = '',
            $status                     = 'Active'
        );
        // Store the current number of active cues
        $prevNumActiveCues = $cues->Data->NumTotalResults;
        // Get the ID of the last cue
        $cueId = $cues->Data->Cues[0]->ID;
        // Cancel it
        $this->client->cancelCue($cueId);

        // Get the updated list of active cues
        $cues = $this->client->getCues(
            $cueId                      = '',
            $groupId                    = '',
            $noteToSelf                 = '',
            $hasPendingCueCompletions   = '',
            $status                     = 'Active'
        );
        $newNumActiveCues = $cues->Data->NumTotalResults;
        $cueId = $cues->Data->Cues[0]->ID;

        // We cancelled an active queue, so we should have one active cue less
        $this->assertEquals($newNumActiveCues, $prevNumActiveCues-1);

    }
}
