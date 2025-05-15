<?php
use App\Http\Controllers\CategoryController;
use App\Models\Person;
use Illuminate\Http\Request;


Route::get('/categories/list', [CategoryController::class, 'list']);
Route::get('/customers/search', function(Request $request) {
    $q = $request->get('q');
    $results = Person::query()
        ->where('name', 'LIKE', "%$q%")
        ->orWhere('company_name', 'LIKE', "%$q%")
        ->limit(10)
        ->get(['id', 'name']);
    return response()->json($results);
});
