<?php

namespace SariwonAPI\Endpoints;

class Server extends AbstractEndpoint {
	
	#[Endpoint('get', '/server/{id}', 'Get Server Infos')]
	public function getServer() {}

	#[Endpoint('post', '/server', 'Create Server')]
	public function createServer() {}

	#[Endpoint('put', '/server/{id}', 'Update Server Infos', 'restricted')]
	public function updateServer() {}

	#[Endpoint('delete', '/server/{id}', 'Delete Server', 'restricted')]
	public function deleteServer() {}

	#[Endpoint('get', '/server/{id}/characters', 'Get Server Characters')]
	public function getServerCharacters() {}

}