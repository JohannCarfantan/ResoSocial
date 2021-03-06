<?php

namespace App\Repositories\User;

use App\User;

class EloquentUserRepository implements UserRepository
{
	/**
	 * Fetch many users by id
	 *
	 * @param array $ids
	 *	
	 * @return mixed
	 */
	public function findManyById(array $ids)
	{
		$users = [];

		foreach ($ids as $id) {
			
			$users[] = User::find($id);	
		}

		return	$users;
	}

	/**
	 * Fetch friends for a user
	 *
	 * @param int $userId
	 *	
	 * @return mixed
	 */
	public function findByIdWithFriends($userId)
	{
        $user = User::with([
			'friends' => function($query){ 
			$query->orderBy('prenom', 'desc');
		}])->findOrFail($userId)->toArray();
		return $user['friends'];
	}
	
}