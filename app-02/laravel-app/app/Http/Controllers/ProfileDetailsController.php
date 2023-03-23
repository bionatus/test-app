<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileDetailsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Update the user's profile details.
     *
     * @return Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email'
        ]);

        if($request->user()->email !== $request->email) {
            $this->validate($request, [
                'email' => 'unique:users'
            ]);
        }

        $args = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'company' => $request->company,
            'hvac_supplier' => $request->hvac_supplier,
            'occupation' => $request->occupation,
            'type_of_services' => $request->type_of_services,
            'references' => $request->references,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip,
            'country' => $request->country,
        ];

        if (!empty($request->password)) {
            $this->validate($request, [
                'password' => 'required_with:repeat_password|same:repeat_password'
            ]);

            $args['password'] = Hash::make(trim($request->password));
        }
        

        $request->user()->forceFill($args)->save();
    }
}