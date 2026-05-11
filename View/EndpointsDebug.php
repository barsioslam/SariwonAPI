<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>EndPoints Debugger</title>
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
	</head>
	<body>

		<div class="endpoints">

			<?php
			$icons = [
				"all" => "lock_open_circle",
				"restricted" => "admin_panel_settings",
				"admin" => "identity_platform"
			];

			$actual_ep = "";

			foreach ($eps as $ep) {
				if ($ep['base'] != $actual_ep) {
					$actual_ep = $ep['base'];
			?>
			<h2><?= $ep['base']; ?></h2>
			<?php
				}
			?>
			<div class="endpoint <?= $ep['method']; ?>">
				<?php if (!empty($ep['access'])) { ?>
				<span class="material-symbols-outlined" title="<?= $ep['access']; ?>"><?= $icons[$ep['access']]; ?></span>
				<?php } ?>
				<?= $ep['url']; ?>
				<span class="desc"><?= $ep['description']; ?></span>
			</div>
			<?php
			}
			?>

		</div>

	</body>
	<style>

		* {
			padding: 0;
			margin: 0;
		}

		body {
			background-color: rgba(25, 25, 25, 1);
			display: flex;
			flex-direction: column;
			color: white;
			font-family: 'Ubuntu', monospace;
		}

		div.endpoints {
			display: flex;
			flex-direction: column;
			gap: 10px;
			padding: 10px;
			margin: 20px;
			border: 2px solid black;
			border-radius: 2px;
		}

		div.endpoints div.endpoint {
			display: flex;
			flex-direction: row;
			padding: 10px;
			gap: 10px;
			border-radius: 5px;
			align-items: center;
		}
		div.endpoints div.endpoint::before {
			padding: 2px;
			text-transform: uppercase;
			width: 80px;
			text-align: center;
			border-radius: 2px;
		}
		div.endpoints div.endpoint.get {
			border: 2px solid #2BACD6;
		}
		div.endpoints div.endpoint.get::before {
			content: 'GET';
			background-color: #2BACD6;
		}
		div.endpoints div.endpoint.post {
			border: 2px solid #2BD653;
		}
		div.endpoints div.endpoint.post::before {
			content: 'POST';
			background-color: #2BD653;
		}
		div.endpoints div.endpoint.put {
			border: 2px solid #DED537;
		}
		div.endpoints div.endpoint.put::before {
			content: 'PUT';
			background-color: #DED537;
		}
		div.endpoints div.endpoint.delete {
			border: 2px solid #DE271F;
		}
		div.endpoints div.endpoint.delete::before {
			content: 'DELETE';
			background-color: #DE271F;
		}
		div.endpoints div.endpoint span.desc {
			color: #BBB;
			background-color: #000;
			padding: 2px 5px;
			border-radius: 2px;
		}

	</style>
</html>