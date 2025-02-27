<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Response;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $baseUrl = "http://localhost";
    protected $user;
    protected $validationErrorMessage = 'The given data was invalid.';

    /**
     * Make an API call
     * @param $method
     * @param $uri
     * @param array $parameters
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param null $content
     * @return \Illuminate\Http\Response
     */
    public function apiCall($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $headers = $this->transformHeadersToServerVars([
            'Accept' => 'application/json'
        ]);
        $server = array_merge($server, $headers);

        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

    /**
     *
     * @return mixed
     */
    public function logInUser($id = 1)
    {
        $user = User::find($id);
        $this->be($user);
        $this->user = $user;
        $this->actingAs($user, 'api');
    }

    /**
     *
     * @param $response
     */
    protected function assertResponseOk($response)
    {
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     *
     * @param $response
     */
    protected function assertResponseInvalid($response)
    {
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    /**
     *
     * @param $response
     */
    protected function assertResponseError($response)
    {
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    /**
     *
     * @param $response
     * @return mixed
     */
    protected function getContent($response)
    {
        return json_decode($response->getContent(), true);
    }

    /**
     *
     * @param $content
     * @param $requiredFields
     */
    protected function checkValidationResponse($content, $requiredFields)
    {
        $this->assertEquals($this->validationErrorMessage, $content['message']);

        foreach($requiredFields as $field) {
            $this->assertArrayHasKey($field, $content['errors']);
        }
    }

    /**
     *
     * @param $budget
     * @param bool $extra
     */
    public function checkBudgetKeysExist($budget, $extra = false)
    {
        $this->assertArrayHasKey('id', $budget);
        $this->assertArrayHasKey('path', $budget);
        $this->assertArrayHasKey('name', $budget);
        $this->assertArrayHasKey('type', $budget);
        $this->assertArrayHasKey('transactionsCount', $budget);

        if ($extra) {
            $this->assertArrayHasKey('amount', $budget);
            $this->assertArrayHasKey('calculatedAmount', $budget);
//            $this->assertArrayHasKey('formattedStartingDate', $budget);
            $this->assertArrayHasKey('spent', $budget);
            $this->assertArrayHasKey('received', $budget);
            $this->assertArrayHasKey('spentOnOrAfterStartingDate', $budget);
            $this->assertArrayHasKey('spentBeforeStartingDate', $budget);
            $this->assertArrayHasKey('receivedOnOrAfterStartingDate', $budget);
            $this->assertArrayHasKey('cumulativeMonthNumber', $budget);
            $this->assertArrayHasKey('cumulative', $budget);
            $this->assertArrayHasKey('remaining', $budget);
        }
    }

    /**
     *
     * @param $transaction
     */
    public function checkTransactionKeysExist($transaction)
    {
        $this->assertArrayHasKey('id', $transaction);
        $this->assertArrayHasKey('path', $transaction);
        $this->assertArrayHasKey('date', $transaction);
        $this->assertArrayHasKey('userDate', $transaction);
        $this->assertArrayHasKey('type', $transaction);
        $this->assertArrayHasKey('description', $transaction);
        $this->assertArrayHasKey('merchant', $transaction);
        $this->assertArrayHasKey('total', $transaction);
        $this->assertArrayHasKey('reconciled', $transaction);
        $this->assertArrayHasKey('allocated', $transaction);
        $this->assertArrayHasKey('validAllocation', $transaction);
        $this->assertArrayHasKey('account_id', $transaction);
        $this->assertArrayHasKey('account', $transaction);
        $this->assertArrayHasKey('budgets', $transaction);
        $this->assertArrayHasKey('multipleBudgets', $transaction);
        $this->assertArrayHasKey('minutes', $transaction);
    }

    /**
     *
     * @param $transaction
     */
    public function checkFavouriteTransactionKeysExist($transaction)
    {
        $this->assertArrayHasKey('id', $transaction);
        $this->assertArrayHasKey('name', $transaction);
        $this->assertArrayHasKey('type', $transaction);
        $this->assertArrayHasKey('description', $transaction);
        $this->assertArrayHasKey('merchant', $transaction);
        $this->assertArrayHasKey('total', $transaction);
        $this->assertArrayHasKey('budgets', $transaction);
        $this->assertArrayHasKey('userDate', $transaction);

        if ($transaction['type'] === 'transfer') {
            $this->assertArrayHasKey('fromAccount', $transaction);
            $this->assertArrayHasKey('toAccount', $transaction);
        }
        else {
            $this->assertArrayHasKey('account', $transaction);
        }
    }

    public function checkSavedFilterKeysExist($filter)
    {
        $this->assertArrayHasKey('id', $filter);
        $this->assertArrayHasKey('name', $filter);
        $this->assertArrayHasKey('filter', $filter);

        $this->assertArrayHasKey('total', $filter['filter']);
        $this->assertArrayHasKey('in', $filter['filter']['total']);
        $this->assertArrayHasKey('out', $filter['filter']['total']);

        $this->assertArrayHasKey('types', $filter['filter']);
        $this->assertArrayHasKey('in', $filter['filter']['types']);
        $this->assertArrayHasKey('out', $filter['filter']['types']);

        $this->assertArrayHasKey('accounts', $filter['filter']);
        $this->assertArrayHasKey('in', $filter['filter']['accounts']);
        $this->assertArrayHasKey('out', $filter['filter']['accounts']);

        $this->assertArrayHasKey('singleDate', $filter['filter']);
        $this->assertArrayHasKey('inSql', $filter['filter']['singleDate']);
        $this->assertArrayHasKey('outSql', $filter['filter']['singleDate']);

        $this->assertArrayHasKey('fromDate', $filter['filter']);
        $this->assertArrayHasKey('inSql', $filter['filter']['fromDate']);
        $this->assertArrayHasKey('outSql', $filter['filter']['fromDate']);

        $this->assertArrayHasKey('toDate', $filter['filter']);
        $this->assertArrayHasKey('inSql', $filter['filter']['toDate']);
        $this->assertArrayHasKey('outSql', $filter['filter']['toDate']);

        $this->assertArrayHasKey('description', $filter['filter']);
        $this->assertArrayHasKey('in', $filter['filter']['description']);
        $this->assertArrayHasKey('out', $filter['filter']['description']);

        $this->assertArrayHasKey('merchant', $filter['filter']);
        $this->assertArrayHasKey('in', $filter['filter']['merchant']);
        $this->assertArrayHasKey('out', $filter['filter']['merchant']);

        $this->assertArrayHasKey('budgets', $filter['filter']);
        $this->assertArrayHasKey('in', $filter['filter']['budgets']);
        $this->assertArrayHasKey('and', $filter['filter']['budgets']['in']);
        $this->assertArrayHasKey('or', $filter['filter']['budgets']['in']);
        $this->assertArrayHasKey('out', $filter['filter']['budgets']);

        $this->assertArrayHasKey('numBudgets', $filter['filter']);
        $this->assertArrayHasKey('in', $filter['filter']['numBudgets']);
        $this->assertArrayHasKey('out', $filter['filter']['numBudgets']);

        $this->assertArrayHasKey('reconciled', $filter['filter']);
        $this->assertArrayHasKey('offset', $filter['filter']);
        $this->assertArrayHasKey('numToFetch', $filter['filter']);
        $this->assertArrayHasKey('displayFrom', $filter['filter']);
        $this->assertArrayHasKey('displayTo', $filter['filter']);
    }

    /**
     *
     * @param $totals
     */
    public function checkTotalsKeysExist($totals)
    {
        $this->assertArrayHasKey('amount', $totals);
        $this->assertArrayHasKey('remaining', $totals);
        $this->assertArrayHasKey('cumulative', $totals);
        $this->assertArrayHasKey('spentBeforeStartingDate', $totals);
        $this->assertArrayHasKey('spentOnOrAfterStartingDate', $totals);
        $this->assertArrayHasKey('receivedOnOrAfterStartingDate', $totals);
    }

    /**
     *
     * @param $totals
     */
    public function checkUserKeysExist($totals)
    {
        $this->assertArrayHasKey('id', $totals);
        $this->assertArrayHasKey('name', $totals);
        $this->assertArrayHasKey('preferences', $totals);
    }

    /**
     *
     * @param $totals
     */
    public function checkBasicTotalKeysExist($totals)
    {
        $this->assertArrayHasKey('credit', $totals);
        $this->assertArrayHasKey('debit', $totals);
        $this->assertArrayHasKey('creditIncludingTransfers', $totals);
        $this->assertArrayHasKey('debitIncludingTransfers', $totals);
        $this->assertArrayHasKey('balance', $totals);
        $this->assertArrayHasKey('reconciled', $totals);
        $this->assertArrayHasKey('numTransactions', $totals);
    }

    /**
     *
     * @param $totals
     */
    public function checkFlexBudgetTotalsKeysExist($totals)
    {
        $this->assertArrayHasKey('allocatedAmount', $totals);
        $this->assertArrayHasKey('allocatedRemaining', $totals);
        $this->assertArrayHasKey('allocatedCalculatedAmount', $totals);
        $this->assertArrayHasKey('spentBeforeStartingDate', $totals);
        $this->assertArrayHasKey('spentOnOrAfterStartingDate', $totals);
        $this->assertArrayHasKey('receivedOnOrAfterStartingDate', $totals);
        $this->assertArrayHasKey('unallocatedAmount', $totals);
        $this->assertArrayHasKey('allocatedPlusUnallocatedAmount', $totals);
        $this->assertArrayHasKey('allocatedPlusUnallocatedCalculatedAmount', $totals);
        $this->assertArrayHasKey('unallocatedCalculatedAmount', $totals);
        $this->assertArrayHasKey('allocatedPlusUnallocatedRemaining', $totals);
        $this->assertArrayHasKey('unallocatedRemaining', $totals);
    }

    /**
     *
     * @param $preferences
     */
    public function checkPreferencesKeysExist($preferences)
    {
        $this->assertArrayHasKey('clearFields', $preferences);
        $this->assertArrayHasKey('colors', $preferences);
        $this->assertArrayHasKey('dateFormat', $preferences);
        $this->assertArrayHasKey('autocomplete', $preferences);
        $this->assertArrayHasKey('description', $preferences['autocomplete']);
        $this->assertArrayHasKey('merchant', $preferences['autocomplete']);
    }

    /**
     *
     * @param $account
     */
    public function checkAccountKeysExist($account)
    {
        $this->assertArrayHasKey('id', $account);
        $this->assertArrayHasKey('name', $account);
//        $this->assertArrayHasKey('balance', $account);
//		$this->assertArrayHasKey('path', $account);
    }

    /**
     *
     * @param $totals
     */
    public function checkAllocationTotalKeysExist($totals)
    {
        $this->assertArrayHasKey('fixedSum', $totals);
        $this->assertArrayHasKey('percentSum', $totals);
        $this->assertArrayHasKey('calculatedAllocationSum', $totals);
    }

    /**
     *
     * @param $graphTotals
     */
    public function checkGraphTotalKeysExist($graphTotals)
    {
        $this->assertArrayHasKey('monthTotals', $graphTotals);

        $monthData = $graphTotals['monthTotals'][0];
        $this->assertArrayHasKey('creditIncludingTransfers', $monthData);
        $this->assertArrayHasKey('debitIncludingTransfers', $monthData);
        $this->assertArrayHasKey('balance', $monthData);
//		$this->assertArrayHasKey('reconciled', $monthData);
//		$this->assertArrayHasKey('numTransactions', $monthData);
        $this->assertArrayHasKey('credit', $monthData);
        $this->assertArrayHasKey('debit', $monthData);
        $this->assertArrayHasKey('month', $monthData);
        $this->assertArrayHasKey('balanceFromBeginning', $monthData);
        $this->assertArrayHasKey('positiveTransferTotal', $monthData);
        $this->assertArrayHasKey('negativeTransferTotal', $monthData);
    }

}
