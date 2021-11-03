<?php

namespace App\Http\Controllers;

use App\Models\Categories\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::all();
    }

    public function listExpense()
    {
        return Category::where('type', 'expense')
            ->get();
    }

    public function listIncome()
    {
        return Category::where('type', 'income')
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $this->validateCategory($request->toArray());
        $category = new Category($validated);
        $category->forceFill($validated);
        $category->save();
        return $category;
    }

    public function show(Category $category)
    {
        return $category;
    }

    protected function categoryRule(Category $category = null)
    {
        return [
            'user_id' => ['empty'],
            'default' => ['nullable'],
            'color' => ['nullable'],
            'icon' => ['nullable'],
            'type' => ['required', 'regex:/^(income|expense)$/i'], // income, expense
            'name' => ['required']
        ];
    }

    protected function validateCategory(array $data, Category $category = null)
    {
        return Validator::make($data, $this->categoryRule($category))
            ->after(function (\Illuminate\Validation\Validator $validator) {
                if ($validator->errors()->any()) return;
                $data = $validator->getData();
                $user = $this->retrieveUser();
                $data['user_id'] = $user->id;

                // format `type` column
                $data['type'] = strtolower($data['type']);

                // format `default` column
                $data['default'] = (preg_match('/^true$/i', $data['default']) === 1) ? true : $data['default'];
                $data['default'] = (preg_match('/^false$/i', $data['default']) === 1) ? false : $data['default'];
                if ($user->cannot('supervisor', Category::class))
                    $data['default'] = false;

                $validator->setData($data);
            })
            ->validate();
    }

    /**
     * Retrieve authed user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|User|null
     */
    protected function retrieveUser()
    {
        return auth()->user();
    }
}
