<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public function index(){
        // dd("here");
        $all_business = Business::get();
        // return view('business.index', compact('all_business'));
        return response()->json($all_business);
    }

    public function store(Request $request){
        // $business = Business::create($request->all());
        // return response()->json($business);
    }

    public function show($id){
        // $business = Business::find($id);
        // return response()->json($business);
    }

    public function update(Request $request, $id){
        // $business = Business::find($id);
        // $business->update($request->all());
        // return response()->json($business);
    }

    public function destroy($id){
        // $business = Business::find($id);
        // $business->delete();
        // return response()->json($business);
    }
}
