<?php

namespace SariwonAPI\Endpoints;

class Map extends AbstractEndpoint {
	
	#[Endpoint('get', '/map/{id}', 'Get Map Infos')]
	public function getMap() {}

	#[Endpoint('post', '/map', 'Create Map')]
	public function createMap() {}

	#[Endpoint('put', '/map/{id}', 'Update Map Infos', 'restricted')]
	public function updateMap() {}

	#[Endpoint('delete', '/map/{id}', 'Delete Map', 'restricted')]
	public function deleteMap() {}

}