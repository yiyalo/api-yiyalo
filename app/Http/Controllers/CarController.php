<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Models\Car;
use JWTAuth;
use Response;
use App\Repository\Transformers\CarTransformer;
use Validator;
use \Illuminate\Http\Response as Res;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Input;
use Auth;

class CarController extends ApiController
{
    //declare the transformer
    protected $carTransformer;

    public function __construct(CarTransformer $carTransformer){
        $this->carTransformer = $carTransformer;
    }

    //To show all the cars with pagination control
    public function index(){
        $limit = Input::get('limit')?:3;

        $cars = Car::with('user')->paginate($limit);

        return $this->respondWithPagination($car, [
            'cars' => $this->carTransformer->transformCollection($cars->all())
        ], 'Record Found!');
    }

    public function show($id){
        $car = Car::with('user')->find($id);

        if(! $car){
            $car = Car::where('%title', $id)->firstOrFail();
        }
        if(count($car) == 0){
            return $this->respondWithError("Car Not Found");
        }

        return $this->respond([
            'status' => 'success',
            'status_code' => $this->getStatusCode(),
            'message' => 'Car Found!',
            'car' => $this->carTransformer->transform($car)
        ]);
    }

    public function store(Request $request){
        $rules = array (
            'api_token' => 'required',
            'title' => 'required',
            'description' => 'required',
            'year' => 'required',
            'price' => 'required',
            'location' => 'required',
        );
        //$api_token = (string)JWTAuth::getToken();

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            return $this->respondValidationError('Fields Validation Failed', $validator->errors());
        }

        $api_token = $request['api_token'];

        try{
            $user = JWTAuth::toUser($api_token);
            
            $car = new Car();
            $car->user_id = $user->id;
            $car->title = $request['title'];
            $car->details = $request['description'];
            $car->cyear = $request['year'];
            $car->price = $request['price'];
            $car->location = $request['location'];
            $car->discount = $request['discount'];
            $car->save();

            return $this->respondCreated('Car is listed successfully!', $this->carTransformer->transform($car));
        }catch(JWTException $e){
            return $this->respondInternalError("An error occured while performing an action!");
        }
    }

    public function update(Request $request){
        $rules = array (
            'api_token' => 'required',
            'title' => 'required',
            'description' => 'required',
            'year' => 'required',
            'price' => 'required',
            'location' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            return $this->respondValidationError('Fields Validation Failed', $validator->errors());
        }

        $api_token = $request['api_token'];

        try{
            $user = JWTAuth::toUser($api_token);

            $car = Car::find($request['id']);
            $car->user_id = $user->id;
            $car->title = $request['title'];
            $car->details = $request['description'];
            $car->cyear = $request['year'];
            $car->price = $request['price'];
            $car->location = $request['location'];
            $car->discount = $request['discount'];
            $car->save();

            return $this->respondCreated('Car is updated successfully!', $this->carTransformer->transform($car));

        }catch(JWTException $e){
            return $this->respondInternalError("An error occurred while performing an action!");
        }
    }

    public function delete($id, $api_token){
        try{
            $user = JWTAuth::toUser($api_token);

            $car = Car::where('id', $id)->where('user_id', $user->id);
            
            $car->delete();

            return $this->respond([

                'status' => 'success',
                'status_code' => $this->getStatusCode(),
                'message' => 'Article deleted successfully!'
            ]);

        }catch(JWTException $e){
            return $this->respondInternalError("An error occurred while performing an action!");
        }
    }

}
