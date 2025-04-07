<?php

namespace App\Http\Controllers;

use App\Models\Column;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ColumnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $columns = Column::orderBy('id', 'desc')->get();
        return view('columns.index', compact('columns'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('columns.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'column' => 'required|string|unique:columns|max:255',
            'status'  => 'required|in:active,deactive',
        ]);

        Column::create($validatedData);

        return redirect()->route('columns.index')->with('success', 'column created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Column $column)
    {
         return view('columns.show', compact('column'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Column $column)
    {
        return view('columns.form', compact('column'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Column $column)
    {
       $validatedData = $request->validate([
            'column' => [
                'required',
                'string',
                'max:255',
                Rule::unique('columns')->ignore($column->id), 
            ],
            'status'  => 'required|in:active,deactive',
        ]);

        $column->update($validatedData);

        return redirect()->route('columns.index')->with('success', 'column updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Column $column)
    {
        $column->delete();
        return redirect()->route('columns.index')->with('success', 'column deleted successfully.');
    }
}
