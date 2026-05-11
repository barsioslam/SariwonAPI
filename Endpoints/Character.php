<?php

namespace SariwonAPI\Endpoints;

class Character extends AbstractEndpoint {
	
	#[Endpoint('get', '/character/{id}', 'Get Character Infos')]
	public function getCharacter() {}

	#[Endpoint('post', '/character', 'Create Character')]
	public function createCharacter() {}

	#[Endpoint('put', '/character/{id}', 'Update Character Infos', 'restricted')]
	public function updateCharacter() {}

	#[Endpoint('delete', '/character/{id}', 'Delete Character', 'restricted')]
	public function deleteCharacter() {}

}