<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('transactions', function(Blueprint $table)
		{
			$table->increments('id')->index();
			$table->integer('user_id')->unsigned()->index();
            //Todo: rename this 'date' column because it is a reserved word in MySql and could cause problems?
            $table->date('date')->index();
			$table->integer('account_id')->unsigned()->index();
			$table->string('type')->index();
			$table->string('description')->nullable()->index();
			$table->string('merchant')->nullable()->index();
			$table->decimal('total', 10, 2)->index();
			$table->boolean('reconciled')->index();
			$table->boolean('allocated')->default(0)->index();
            $table->integer('minutes')->nullable()->index();
			$table->timestamps();

			$table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('transactions');
	}

}
