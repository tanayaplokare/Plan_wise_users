<?php

namespace App\Http\Controllers;

use App\Models\Keyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KeywordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $keywords = Keyword::orderBy('id', 'desc')->get();
        return view('keywords.index', compact('keywords'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('keywords.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'keyword' => 'required|string|unique:keywords|max:255',
            'status'  => 'required|in:active,deactive',
        ]);

        Keyword::create($validatedData);

        return redirect()->route('keywords.index')->with('success', 'Keyword created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Keyword $keyword)
    {
         return view('keywords.show', compact('keyword'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Keyword $keyword)
    {
        return view('keywords.form', compact('keyword'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Keyword $keyword)
    {
       $validatedData = $request->validate([
            'keyword' => [
                'required',
                'string',
                'max:255',
                Rule::unique('keywords')->ignore($keyword->id), 
            ],
            'status'  => 'required|in:active,deactive',
        ]);

        $keyword->update($validatedData);

        return redirect()->route('keywords.index')->with('success', 'Keyword updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Keyword $keyword)
    {
        $keyword->delete();
        return redirect()->route('keywords.index')->with('success', 'Keyword deleted successfully.');
    }
}
