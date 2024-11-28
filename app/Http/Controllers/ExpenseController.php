<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::latest()->get();
        return response()->json(ExpenseResource::collection($expenses), 200);
    }

    public function store(StoreExpenseRequest $request)
    {
        $expense = Expense::create($request->validated());
        return response()->json(new ExpenseResource($expense), 201);
    }

    public function show(Expense $expense)
    {
        return response()->json(new ExpenseResource($expense), 200);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $expense->update($request->validated());
        return response()->json(new ExpenseResource($expense), 200);
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return response()->json(null, 204);
    }
}