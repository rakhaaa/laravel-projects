<?php

use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can get all expenses', function () {
    Expense::factory()->count(5)->create();

    $response = $this->getJson('/api/expenses');

    $response->assertStatus(200)
             ->assertJsonCount(5);
});

test('can create an expense', function () {
    $expenseData = [
        'description' => 'Lunch',
        'amount' => 10.5,
        'category' => 'Food',
        'date' => now(),
    ];

    $response = $this->postJson('/api/expenses', $expenseData);

    $response->assertStatus(201)
             ->assertJsonFragment($expenseData);
});

test('can get a single expense', function () {
    $expense = Expense::factory()->create();

    $response = $this->getJson("/api/expenses/{$expense->id}");

    $response->assertStatus(200)
             ->assertJson([
                 'id' => $expense->id,
                 'description' => $expense->description,
                 'amount' => $expense->amount,
                 'category' => $expense->category,
                 'date' => $expense->date,
             ]);
});

test('can update an expense', function () {
    $expense = Expense::factory()->create();

    $updatedExpenseData = [
        'description' => 'Dinner',
        'amount' => 20.5,
        'category' => 'Food',
        'date' => now(),
    ];

    $response = $this->putJson("/api/expenses/{$expense->id}", $updatedExpenseData);

    $response->assertStatus(200)
             ->assertJsonFragment($updatedExpenseData);
});

test('can delete an expense', function () {
    $expense = Expense::factory()->create();

    $response = $this->deleteJson("/api/expenses/{$expense->id}");

    $response->assertStatus(204);
});