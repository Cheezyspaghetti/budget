<?php

use App\Models\Savings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * Class TransactionsStoreTest
 */
class TransactionsStoreTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * @return void
     */
    public function it_can_create_a_transfer_transaction_from_an_account()
    {
        DB::beginTransaction();
        $this->logInUser();

        $transaction = [
            'date' => '2015-01-01',
            'account_id' => 1,
            'type' => 'transfer',
            'direction' => 'from',
            'description' => 'interesting description',
            'merchant' => 'some store',
            'total' => 5,
            'reconciled' => 0,
            'allocated' => 0
        ];

        $response = $this->call('POST', '/api/transactions', $transaction);
        $content = json_decode($response->getContent(), true);
     // dd($content);

        $this->checkTransactionKeysExist($content);

        $this->assertEquals('2015-01-01', $content['date']);
        $this->assertEquals('1', $content['account_id']);
        $this->assertEquals('transfer', $content['type']);
        $this->assertEquals('interesting description', $content['description']);
        $this->assertEquals('some store', $content['merchant']);
        $this->assertEquals('-5', $content['total']);
        $this->assertEquals(0, $content['reconciled']);
        $this->assertEquals(0, $content['allocated']);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        DB::rollBack();
    }

    /**
     * @test
     * @return void
     */
    public function it_can_create_a_transfer_transaction_to_an_account()
    {
        DB::beginTransaction();
        $this->logInUser();

        $transaction = [
            'date' => '2015-01-01',
            'account_id' => 1,
            'type' => 'transfer',
            'direction' => 'to',
            'description' => 'interesting description',
            'merchant' => 'some store',
            'total' => 5,
            'reconciled' => 0,
            'allocated' => 0
        ];

        $response = $this->call('POST', '/api/transactions', $transaction);
        $content = json_decode($response->getContent(), true);
        // dd($content);

        $this->checkTransactionKeysExist($content);

        $this->assertEquals('2015-01-01', $content['date']);
        $this->assertEquals('1', $content['account_id']);
        $this->assertEquals('transfer', $content['type']);
        $this->assertEquals('interesting description', $content['description']);
        $this->assertEquals('some store', $content['merchant']);
        $this->assertEquals('5', $content['total']);
        $this->assertEquals(0, $content['reconciled']);
        $this->assertEquals(0, $content['allocated']);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        DB::rollBack();
    }

    /**
     * @test
     * @return void
     */
    public function it_inserts_an_income_transaction_and_increases_the_savings()
    {
        $this->logInUser();

        $this->assertEquals('50.00', Savings::forCurrentUser()->first()->amount);

        $transaction = [
            'date' => '2015-01-01',
            'account_id' => 1,
            'type' => 'income',
            'description' => 'interesting description',
            'merchant' => 'some store',
            'total' => 5,
            'reconciled' => 0,
            'allocated' => 0,
            'minutes' => 70,
            'budget_ids' => [2,4]
        ];

        $response = $this->apiCall('POST', '/api/transactions', $transaction);
        $content = json_decode($response->getContent(), true);
//        dd($content);

        $this->assertEquals(201, $response->getStatusCode());

//        dd($content);

        $this->assertEquals('2015-01-01', $content['date']);
        $this->assertEquals(1, $content['account_id']);
        $this->assertEquals('income', $content['type']);
        $this->assertEquals('interesting description', $content['description']);
        $this->assertEquals('some store', $content['merchant']);
        $this->assertEquals(5, $content['total']);
        $this->assertEquals(0, $content['reconciled']);
        $this->assertEquals(0, $content['allocated']);
        $this->assertEquals(2, $content['budgets'][0]['id']);
        $this->assertEquals(4, $content['budgets'][1]['id']);
        $this->assertCount(2, $content['budgets']);

        $this->checkTransactionKeysExist($content);

        //Check the allocation was done correctly (100% of the transaction to the first budget)
        $this->assertNull($content['budgets'][0]['pivot']['allocated_fixed']);
        $this->assertEquals('100.00', $content['budgets'][0]['pivot']['allocated_percent']);
        $this->assertEquals('5.00', $content['budgets'][0]['pivot']['calculated_allocation']);

        $this->assertNull($content['budgets'][1]['pivot']['allocated_fixed']);
        $this->assertEquals('0.00', $content['budgets'][1]['pivot']['allocated_percent']);
        $this->assertEquals('0.00', $content['budgets'][1]['pivot']['calculated_allocation']);

        $this->assertEquals('2015-01-01', $content['date']);
        $this->assertEquals('1', $content['account_id']);
        $this->assertEquals('income', $content['type']);
        $this->assertEquals('interesting description', $content['description']);
        $this->assertEquals('some store', $content['merchant']);
        $this->assertEquals('5', $content['total']);
        $this->assertEquals(0, $content['reconciled']);
        $this->assertEquals(0, $content['allocated']);
        $this->assertEquals(70, $content['minutes']);
        $this->assertEquals('business', $content['budgets'][0]['name']);
        $this->assertEquals('busking', $content['budgets'][1]['name']);

        //Check the savings increased
        $this->assertEquals('50.50', Savings::forCurrentUser()->first()->amount);
    }

    /**
     * @test
     */
    public function it_inserts_an_expense_transaction_and_does_not_change_the_savings()
    {
        $this->logInUser();

        $this->assertEquals('50.00', Savings::forCurrentUser()->first()->amount);

        $transaction = [
            'date' => '2015-01-01',
            'account_id' => 1,
            'type' => 'expense',
            'description' => 'interesting description',
            'merchant' => 'some store',
            'total' => -5,
            'reconciled' => 0,
            'allocated' => 0,
            'budget_ids' => [2,4]
        ];

        $response = $this->apiCall('POST', '/api/transactions', $transaction);
        $content = json_decode($response->getContent(), true);

        $this->assertEquals(201, $response->getStatusCode());

        $this->assertEquals('2015-01-01', $content['date']);
        $this->assertEquals(1, $content['account_id']);
        $this->assertEquals('expense', $content['type']);
        $this->assertEquals('interesting description', $content['description']);
        $this->assertEquals('some store', $content['merchant']);
        $this->assertEquals(-5, $content['total']);
        $this->assertEquals(0, $content['reconciled']);
        $this->assertEquals(0, $content['allocated']);
        $this->assertEquals(2, $content['budgets'][0]['id']);
        $this->assertEquals(4, $content['budgets'][1]['id']);
        $this->assertCount(2, $content['budgets']);

        $this->checkTransactionKeysExist($content);

        $this->assertEquals('2015-01-01', $content['date']);
        $this->assertEquals('1', $content['account_id']);
        $this->assertEquals('expense', $content['type']);
        $this->assertEquals('interesting description', $content['description']);
        $this->assertEquals('some store', $content['merchant']);
        $this->assertEquals('-5', $content['total']);
        $this->assertEquals(0, $content['reconciled']);
        $this->assertEquals(0, $content['allocated']);
        $this->assertEquals('business', $content['budgets'][0]['name']);
        $this->assertEquals('busking', $content['budgets'][1]['name']);

        //Check the savings remained the same
        $this->assertEquals('50.00', Savings::forCurrentUser()->first()->amount);
    }

    /**
     * Todo: Do the same for checking total is positive for income transactions, and also check these things when transaction is updated
     * @test
     * @return void
     */
    public function it_converts_a_positive_total_to_negative_when_inserting_an_expense_transaction()
    {
        $this->logInUser();

        $this->assertEquals('50.00', Savings::forCurrentUser()->first()->amount);

        $transaction = [
            'date' => '2015-01-01',
            'account_id' => 1,
            'type' => 'expense',
            'description' => 'interesting description',
            'merchant' => 'some store',
            'total' => 5,
            'reconciled' => 0,
            'allocated' => 0,
            'budget_ids' => [2,4]
        ];

        $response = $this->apiCall('POST', '/api/transactions', $transaction);
        $content = json_decode($response->getContent(), true);

        $this->assertEquals(201, $response->getStatusCode());

        $this->assertEquals(2, $content['budgets'][0]['id']);
        $this->assertEquals(4, $content['budgets'][1]['id']);
        $this->assertCount(2, $content['budgets']);

        $this->checkTransactionKeysExist($content);

        $this->assertEquals('2015-01-01', $content['date']);
        $this->assertEquals('1', $content['account_id']);
        $this->assertEquals('expense', $content['type']);
        $this->assertEquals('interesting description', $content['description']);
        $this->assertEquals('some store', $content['merchant']);
        $this->assertEquals('-5', $content['total']);
        $this->assertEquals(0, $content['reconciled']);
        $this->assertEquals(0, $content['allocated']);
        $this->assertEquals('business', $content['budgets'][0]['name']);
        $this->assertEquals('busking', $content['budgets'][1]['name']);

        //Check the savings remained the same
        $this->assertEquals('50.00', Savings::forCurrentUser()->first()->amount);
    }
}