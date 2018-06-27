<?php

namespace App\Repository\Transformers;

class CarTransformer extends Transformer{

    public function transform($car){
        return [
            'id' => $car->id,
            'title' => $car->title,
            'description' => $car->details,
            'year' => $car->cyear,
            'price' => $car->price,
            'location' => $car->location,
            'discount' => $car->discount,
            'user' => [
                'id' => $car->user->id,
                'fullname' => $car->user->name,
                'email' => $car->user->email,
            ]
        ];
    }
}