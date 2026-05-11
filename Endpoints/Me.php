<?php

namespace SariwonAPI\Endpoints;

class Me extends AbstractEndpoint {
	
	#[Endpoint('get', '/me', 'Get Self User Infos')]
	public function getMe() {
		return [
			'id' => 1,
			'username' => 'craft',
			'email' => 'crafterhide.ytb@gmail.com'
		];
	}

	#[Endpoint('put', '/me', 'Update Self User Infos')]
	public function updateMe() {}

	#[Endpoint('delete', '/me', 'Delete Self User')]
	public function deleteMe() {}

}