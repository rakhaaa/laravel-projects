### Step-by-Step Guide

#### Step 1: Project Initialization

1. **Install Laravel and Initialize the Project**:
   ```sh
   laravel new expense-tracker
   cd expense-tracker
   ```

2. **Generate the Model with All Components Including Resource**:
   ```sh
   php artisan make:model Expense -aR
   ```

#### Step 2: Migration

1. **Edit the Migration File**:
   Define the structure for the `expenses` table.
   ```php
   <?php

   use Illuminate\Database\Migrations\Migration;
   use Illuminate\Database\Schema\Blueprint;
   use Illuminate\Support\Facades\Schema;

   class CreateExpensesTable extends Migration
   {
       public function up()
       {
           Schema::create('expenses', function (Blueprint $table) {
               $table->uuid('id')->primary();
               $table->string('description');
               $table->decimal('amount', 8, 2);
               $table->string('category');
               $table->date('date');
               $table->timestamps();
           });
       }

       public function down()
       {
           Schema::dropIfExists('expenses');
       }
   }
   ```

2. **Run the Migration**:
   ```sh
   php artisan migrate
   ```

#### Step 3: Model

1. **Update the Expense Model**:
   ```php
   <?php

   namespace App\Models;

   use Illuminate\Database\Eloquent\Factories\HasFactory;
   use Illuminate\Database\Eloquent\Model;
   use Illuminate\Database\Eloquent\Concerns\HasUuids;

   class Expense extends Model
   {
       use HasFactory, HasUuids;

       protected $keyType = 'string';
       public $incrementing = false;

       protected $fillable = [
           'description', 'amount', 'category', 'date',
       ];
   }
   ```

#### Step 4: Factory

1. **Define the Factory**:
   ```php
   <?php

   namespace Database\Factories;

   use App\Models\Expense;
   use Illuminate\Database\Eloquent\Factories\Factory;

   class ExpenseFactory extends Factory
   {
       protected $model = Expense::class;

       public function definition()
       {
           return [
               'description' => fake()->sentence(),
               'amount' => fake()->randomFloat(2, 1, 1000),
               'category' => fake()->word(),
               'date' => fake()->date(),
           ];
       }
   }
   ```

#### Step 5: Seeder

1. **Define the Seeder**:
   ```php
   <?php

   namespace Database\Seeders;

   use App\Models\Expense;
   use Illuminate\Database\Seeder;

   class ExpenseSeeder extends Seeder
   {
       public function run()
       {
           Expense::factory()->count(50)->create();
       }
   }
   ```

2. **Run the Seeder**:
   ```sh
   php artisan db:seed --class=ExpenseSeeder
   ```

#### Step 6: Form Requests

1. **Define Validation Rules for Store Request**:
   ```php
   <?php

   namespace App\Http\Requests;

   use Illuminate\Foundation\Http\FormRequest;

   class StoreExpenseRequest extends FormRequest
   {
       public function authorize()
       {
           return true;
       }

       public function rules()
       {
           return [
               'description' => 'required|string|max:255',
               'amount' => 'required|numeric',
               'category' => 'required|string|max:255',
               'date' => 'required|date',
           ];
       }
   }
   ```

2. **Define Validation Rules for Update Request**:
   ```php
   <?php

   namespace App\Http\Requests;

   use Illuminate\Foundation\Http\FormRequest;

   class UpdateExpenseRequest extends FormRequest
   {
       public function authorize()
       {
           return true;
       }

       public function rules()
       {
           return [
               'description' => 'sometimes|required|string|max:255',
               'amount' => 'sometimes|required|numeric',
               'category' => 'sometimes|required|string|max:255',
               'date' => 'sometimes|required|date',
           ];
       }
   }
   ```

#### Step 7: Resource

1. **Create a Resource**:
   ```php
   <?php

   namespace App\Http\Resources;

   use Illuminate\Http\Resources\Json\JsonResource;

   class ExpenseResource extends JsonResource
   {
       public function toArray($request)
       {
           return [
               'id' => $this->id,
               'description' => $this->description,
               'amount' => $this->amount,
               'category' => $this->category,
               'date' => $this->date,
           ];
       }
   }
   ```

#### Step 8: Controller

1. **Implement CRUD Operations**:
   ```php
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
           $expenses = Expense::all();
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
   ```

#### Step 9: Routes

1. **Define Routes**:
   ```php
   <?php

   use Illuminate\Support\Facades\Route;
   use App\Http\Controllers\ExpenseController;

   Route::prefix('api')->group(function () {
       Route::resource('expenses', ExpenseController::class)->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
   });
   ```

#### Step 10: Testing with Pest

1. **Write Test Methods**:
   ```php
   <?php

   use App\Models\Expense;
   use Illuminate\Foundation\Testing\RefreshDatabase;

   uses(RefreshDatabase::class);

   it('can get all expenses', function () {
       Expense::factory()->count(5)->create();

       $response = $this->getJson('/api/expenses');

       $response->assertStatus(200)
                ->assertJsonCount(5);
   });

   it('can create an expense', function () {
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

   it('can get a single expense', function () {
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

   it('can update an expense', function () {
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

   it('can delete an expense', function () {
       $expense = Expense::factory()->create();

       $response = $this->deleteJson("/api/expenses/{$expense->id}");

       $response->assertStatus(204);
   });
   ```

### Ensure Testing Environment

1. **Ensure `.env.testing` Configuration**:
   Make sure your `.env.testing` is set up for SQLite in-memory database.

   Example content for `.env.testing`:
   ```ini
   APP_ENV=testing
   APP_KEY=base64:+moJWsFXn3QCKrQCxORkEkFshYL/cYmk9AjfB1dfhjw=
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   DB_CONNECTION=sqlite
   DB_DATABASE=:memory:

   CACHE_DRIVER=file
   QUEUE_CONNECTION=sync
   ```

This setup ensures that your Expense Tracker project is structured properly, tests are safe with an in-memory database, and follows your guidelines.
