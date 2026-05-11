<?php

namespace SariwonAPI\Endpoints;

class User extends AbstractEndpoint {
	
	#[Endpoint('get', '/user/{id}', 'Get User Infos', 'admin')]
	public function getUser() {}

	#[Endpoint('post', '/user', 'Create User')]
	public function createUser() {}

	#[Endpoint('put', '/user/{id}', 'Update User Infos', 'admin')]
	public function updateUser() {}

	#[Endpoint('delete', '/user/{id}', 'Delete User', 'admin')]
	public function deleteUser() {}

}