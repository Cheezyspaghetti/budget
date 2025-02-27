<?php

use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * Each month, change starting date in config/budgets.php
 * in order for tests to pass.
 * Class BudgetsUpdateTest
 */
class BudgetsUpdateTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Each month I will need to change the starting date
     * in order for the test to pass.
     * @test
     * @return void
     */
    public function it_updates_a_fixed_budget()
    {
        $this->logInUser();

        $budget = Budget::find(2);

        $startingDate = Carbon::today()->subMonths(2);

        $response = $this->apiCall('PUT', '/api/budgets/'.$budget->id, [
            'name' => 'jetskiing',
            'amount' => 10,
            'starting_date' => $startingDate->copy()->format('Y-m-d')
        ]);

        $content = $this->getContent($response);
//        dd($content);

        $this->checkBudgetKeysExist($content, true);

        $this->assertEquals(2, $content['id']);
        $this->assertEquals('http://localhost/api/budgets/2', $content['path']);
        $this->assertEquals('jetskiing', $content['name']);
        $this->assertEquals(10, $content['amount']);
//        $this->assertEquals(20, $content['calculatedAmount']);
        $this->assertEquals('fixed', $content['type']);
//        $this->assertEquals($startingDate->copy()->format('d/m/y'), $content['formattedStartingDate']);

        $this->assertEquals(-70, $content['spent']);
        $this->assertEquals(300, $content['received']);
        $this->assertEquals(0, $content['spentOnOrAfterStartingDate']);
        $this->assertEquals(-70, $content['spentBeforeStartingDate']);
        $this->assertEquals(0, $content['receivedOnOrAfterStartingDate']);
        $this->assertEquals(3, $content['cumulativeMonthNumber']);
        $this->assertEquals(30, $content['cumulative']);
        $this->assertEquals(30, $content['remaining']);
        $this->assertEquals(6, $content['transactionsCount']);

        $this->assertEquals(200, $response->getStatusCode());

//        $date = Carbon::parse($content['starting_date']['date']);
//        $this->assertEquals('2016-01-01', $date->format('Y-m-d'));
    }

    /**
     * @test
     * @return void
     */
    public function it_updates_a_flex_budget()
    {
        $this->logInUser();

        $budget = Budget::find(4);

        $response = $this->apiCall('PUT', '/api/budgets/'.$budget->id, [
            'name' => 'busking stuff',
            'amount' => 20,
            //Changing the starting date here changes the remaining balance
//            'starting_date' => '2015-10-01'
        ]);

        $content = $this->getContent($response);
//        dd($content);

        $this->checkBudgetKeysExist($content, true);

        $this->assertEquals(4, $content['id']);
        $this->assertEquals('http://localhost/api/budgets/4', $content['path']);
        $this->assertEquals('busking stuff', $content['name']);
        $this->assertEquals(20, $content['amount']);
        $this->assertEquals(40, $content['calculatedAmount']);
        $this->assertEquals('flex', $content['type']);

        //Todo:
//        $this->assertEquals('01/10/15', $content['formattedStartingDate']);

//        $this->assertEquals(-70, $content['spent']);
//        $this->assertEquals(300, $content['received']);
//        $this->assertEquals(0, $content['spentOnOrAfterStartingDate']);
//        $this->assertEquals(-70, $content['spentBeforeStartingDate']);
//        $this->assertEquals(0, $content['receivedOnOrAfterStartingDate']);
//        $this->assertEquals(1, $content['cumulativeMonthNumber']);
//        $this->assertEquals(30, $content['cumulative']);
//        $this->assertEquals(30, $content['remaining']);
//        $this->assertEquals(6, $content['transactionsCount']);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Todo: Test changing to/from other types.
     * @test
     * @return void
     */
    public function it_changes_a_budget_type_from_fixed_to_flex()
    {
        $this->logInUser();

        $budget = Budget::find(2);

        $this->assertEquals('fixed', $budget->type);

        $response = $this->apiCall('PUT', '/api/budgets/'.$budget->id, [
            'type' => 'flex',
        ]);

        $content = json_decode($response->getContent(), true);
//        dd($content);

        $this->checkBudgetKeysExist($content, true);

        $this->assertEquals(2, $content['id']);
        $this->assertEquals('http://localhost/api/budgets/2', $content['path']);
        $this->assertEquals('business', $content['name']);
        $this->assertEquals(100, $content['amount']);

        // Why 1300? When the type is changed from fixed to flex:
        // Remaining fixed budget changes by 1060 (increasing remaining balance by 1060)
        // Expenses with fixed budget before starting date changes by 30, increasing remaining balance by 30
        // Expenses with fixed budget after starting date changes by 40, increasing remaining balance by 40
        // Expenses with flex budget after before date changes by 30, decreasing remaining balance by 30
        // The remaining balance was initially 200
        // 200 + 1060 + 30 + 40 - 30 = 1300
        // The amount of the budget is 100%, so it should be the same as the remaining balance.
        $this->assertEquals(1300, $content['calculatedAmount']);

        $this->assertEquals('flex', $content['type']);

        $startingDate = Carbon::createFromFormat('Y-m-d', Config::get('budgets.startingDate'))->format('d/m/y');
//        $this->assertEquals($startingDate, $content['formattedStartingDate']);

        $this->assertEquals(-70, $content['spent']);
        $this->assertEquals(300, $content['received']);
        $this->assertEquals(-40, $content['spentOnOrAfterStartingDate']);
        $this->assertEquals(-30, $content['spentBeforeStartingDate']);
        $this->assertEquals(200, $content['receivedOnOrAfterStartingDate']);
        $this->assertEquals(9, $content['cumulativeMonthNumber']);
        $this->assertNull($content['cumulative']);
        $this->assertEquals(1460, $content['remaining']);
        $this->assertEquals(6, $content['transactionsCount']);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     * @return void
     */
    public function it_changes_a_budget_type_from_unassigned_to_fixed()
    {
        $this->logInUser();

        $budget = Budget::find(1);

        $this->assertEquals('unassigned', $budget->type);

        $startingDate = Carbon::today()->subMonths(14);
        $response = $this->apiCall('PUT', '/api/budgets/'.$budget->id, [
            'type' => 'fixed',
            'starting_date' => $startingDate->copy()->format('Y-m-d'),
            'amount' => 5
        ]);

        $content = json_decode($response->getContent(), true);
//        dd($content);

        $this->checkBudgetKeysExist($content, true);

        $this->assertEquals(1, $content['id']);
        $this->assertEquals('http://localhost/api/budgets/1', $content['path']);
        $this->assertEquals('bank fees', $content['name']);
        $this->assertEquals(5, $content['amount']);

//        $this->assertEquals(1300, $content['calculatedAmount']);

        $this->assertEquals('fixed', $content['type']);
//        $this->assertEquals($startingDate->copy()->format('d/m/y'), $content['formattedStartingDate']);

        $this->assertEquals(-5, $content['spent']);
        $this->assertEquals(0, $content['received']);
        $this->assertEquals(-5, $content['spentOnOrAfterStartingDate']);
        $this->assertEquals(0, $content['spentBeforeStartingDate']);
        $this->assertEquals(0, $content['receivedOnOrAfterStartingDate']);
        $this->assertEquals(15, $content['cumulativeMonthNumber']);
        $this->assertEquals(75, $content['cumulative']);
        $this->assertEquals(70, $content['remaining']);
        $this->assertEquals(1, $content['transactionsCount']);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     * @return void
     */
    public function it_updates_an_unassigned_budget()
    {
        $this->logInUser();

        $budget = Budget::forCurrentUser()->where('type', 'unassigned')->first();

        $response = $this->apiCall('PUT', '/api/budgets/'.$budget->id, [
            'name' => 'bananas'
        ]);

        $content = json_decode($response->getContent(), true);

        $this->checkBudgetKeysExist($content, true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('bananas', $content['name']);
    }
}