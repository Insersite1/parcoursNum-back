<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use App\Models\Action;
use App\Http\Requests\StoreActionRequest;
use App\Http\Requests\UpdateActionRequest;
=======
use Illuminate\Http\Request;
>>>>>>> 7b6ffc5 (Ajout de ActionController)

class ActionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
<<<<<<< HEAD
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreActionRequest $request)
=======
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
>>>>>>> 7b6ffc5 (Ajout de ActionController)
    {
        //
    }

    /**
     * Display the specified resource.
     */
<<<<<<< HEAD
    public function show(Action $action)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Action $action)
=======
    public function show(string $id)
>>>>>>> 7b6ffc5 (Ajout de ActionController)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
<<<<<<< HEAD
    public function update(UpdateActionRequest $request, Action $action)
=======
    public function update(Request $request, string $id)
>>>>>>> 7b6ffc5 (Ajout de ActionController)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
<<<<<<< HEAD
    public function destroy(Action $action)
=======
    public function destroy(string $id)
>>>>>>> 7b6ffc5 (Ajout de ActionController)
    {
        //
    }
}
