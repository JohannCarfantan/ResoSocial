<?php namespace App\Repositories\User;

use App\User;

interface UserRepository
{	
	public function getPaginated($howMany, $byKeyword);
	public function findById($id);
	public function findManyById(array $ids);
	public function findByIdWithFriends($userId);
}