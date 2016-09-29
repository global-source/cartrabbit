<?php

namespace CartRabbit\Requests;

class Orders{

    public function rules(){
        return [
            'firstName' => 'required',
            'lastName' => 'required',
            'caddress' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'address' => 'required'
        ];
    }
}