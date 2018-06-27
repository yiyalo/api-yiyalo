<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use JWTAuth;
use Response;
use App\Repository\Transformers\UserTransformer;
use \Illuminate\Http\Response as Res;
use Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends ApiController
{
    //declare userTransformer
    protected $userTransformer;

    public function __construct(userTransformer $userTransformer){
        $this->userTransformer = $userTransformer;
    }

    //Api user authenticate method
    //return @JSON message
    public function authenticate(Request $request){
        $rules = array(
            'email' => 'required|email',
            'password' => 'required',
        );

        //Check the request with the rules
        $validator = Validator::make($request->all(), $rules);

        //If validation process fail or success
        if($validator->fails()){
            return $this->respondValidationError('Fields Validation Failed!', $validator->errors());
        }else{
            $user = User::where('email', $request['email'])->first();
            
            if($user){
                $api_token = $user->api_token;

                //if no api_token obtained...
                if($api_token == NULL){
                    return $this->_login($request['email'], $request['password']);
                }
                try{
                    $user = JWTAuth::toUser($api_token);
                    return $this->respond([
                        'status' => 'success',
                        'status_code' => $this->getStatusCode(),
                        'message' => 'Already Logged In',
                        'user' => $this->userTransformer->transform($user),
                    ]); 
                }catch(JWTException $e){
                    $user->api_token = NULL;
                    $user->save();
                    return $this->respondInternalError('Login Unsuccessful. An Error Occured!');
                }
            }else{
                return $this->respondWithError('Invalid Email or Password');
            }
        }
    }

    private function _login($email, $password){
        $credentials = ['email' => $email, 'password' => $password];

        if(! $token = JWTAuth::attempt($credentials)){
            return $this->respondWithError('User Does Not Exist!');
        }
        
        $user = JWTAuth::toUser($token);
        $user->api_token = $token;
        $user->save();
        return $this->respond([
            'status' => 'success',
            'statuts_code' => $this->getStatusCode(),
            'message' => 'Login Successful',
            'data' => $this->userTransformer->transform($user),
        ]);
    }

    public function register(Request $request){
        $rules = array(
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:3'
        );

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            return $this->respondValidationError('Fields Validation Failed', $validator->errors());
        }else{
            $user = User::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'password' => \Hash::make($request['password']),
            ]);
            return $this->_login($request['email'], $request['password']);
        }
    }

    public function logout($api_token){
        try{
            $user = JWTAuth::toUser($api_token);
            $user->api_token = NULL;
            $user->save();

            JWTAuth::setToken($api_token)->invalidate();
            $this->setStatusCode(Res::HTTP_OK);
            return $this->respond([
                'status' => 'success',
                'status_code' => $this->getStatusCode(),
                'message' => 'Logout Successful!',
            ]);
        }catch(JWTException $e){
            return $this->respondInternalError('An Error Occured While Performing An Action!');
        }
    }
}
