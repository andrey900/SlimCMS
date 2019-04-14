<?php

namespace App\Models;

class Users extends BaseModel
{
	protected $table = 'users';

	protected $fillable = ['login', 'email', 'password', 'active'];

	public function update(array $arData = [], array $options = []){
		if( empty($arData['password']) ){
			unset($arData['password']);
		}

		parent::update($arData);
	}

	public function save(array $options = []){
		if( isset($this->password) && strcmp($this->getOriginal('password'), $this->password)!==0 )
			$this->password = self::makePass($this->password);

		parent::save($options);
	}

	public function verifyPassword($pass){
		return password_verify($pass, $this->password);
	}

	public static function makePass($pass){
		$options = ['cost' => 12];

		return password_hash($pass, PASSWORD_BCRYPT, $options);
	}
}