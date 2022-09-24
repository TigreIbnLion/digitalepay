<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User
     */
    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make($request->all(),
            [
                'nom'=> 'required',
                'prenom'=> 'required',
                'date_naissance'=> 'required',
                'lieu_naissance'=> 'required',
                'sexe'=> 'required',
                'domicile'=> 'required',
                'numero' => 'required|unique:users,numero',
                'email' => 'required|email|unique:users,email',
                'password' => 'required',
                'photo' => "required|file",
                'piece' => "required|file",
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            $piece = $request->file('piece');
            $nom_piece = $piece->getClientOriginalName().time();
            $destinationPath = 'imgPiece';
            $piece->move($destinationPath,$piece->getClientOriginalName());
            $photo = $request->file('photo');
            $nom_photo = $photo->getClientOriginalName().time();
            $destination2Path = 'imgPhoto';
            $photo->move($destination2Path,$photo->getClientOriginalName());

            $user = User::create([
                'nom'=> $request->nom,
                'prenom'=> $request->prenom,
                'date_naissance'=> $request->date_naissance,
                'lieu_naissance'=> $request->lieu_naissance,
                'sexe'=> $request->sexe,
                'domicile'=> $request->domicile,
                'numero' => $request->numero,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'photo' => $nom_photo,
                'piece' => $nom_piece
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(),
            [
                'numero' => 'required',
                'password' => 'required'
            ]);
            // $email = User::where('numero',$request->numero)->first();
            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',

                    'errors' => $validateUser->errors()
                ], 401);
            }

            if(!Auth::attempt(['numero'=>$request->numero,'password'=>$request->password])){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('numero',$request->numero )->first();


            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
