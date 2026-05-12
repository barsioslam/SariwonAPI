<?php

namespace SariwonAPI\Endpoints;

use SariwonAPI\Models\ServerModel;
use SariwonAPI\Models\SpeciesModel;
use SariwonAPI\Models\CharacterClassModel;
use SariwonAPI\Models\CharacterJobModel;
use SariwonAPI\Models\AlignmentModel;
use SariwonAPI\Config\ErrorCodes;

class ServerLookup extends AbstractEndpoint {

    private function requireGm(int $serverId): void {
        $userId = $this->requireAuth();
        $model  = new ServerModel();
        if (!$model->isOwner($serverId, $userId)) {
            jsonResponse(false, [], ErrorCodes::SERVER_FORBIDDEN, 403);
        }
    }

    private function checkOwnsLookup(array $row, int $serverId): void {
        if ((int)($row['server_id'] ?? 0) !== $serverId) {
            jsonResponse(false, [], ErrorCodes::SERVER_FORBIDDEN, 403);
        }
    }

    /* ── ESPÈCES ── */

    #[Endpoint('post', '/server/{id}/species', 'Create species')]
    public function createSpecies(string $id): array {
        $serverId = (int) $id;
        $userId   = $this->requireAuth();
        $this->requireGm($serverId);
        $body = $this->body();

        $name = trim((string)($body['name'] ?? ''));
        if ($name === '') jsonResponse(false, [], ErrorCodes::MISSING_PARAMETERS, 400);

        $model = new SpeciesModel();
        $newId = $model->create($name, $body['description'] ?? null, $serverId, $userId);
        return $model->findById($newId);
    }

    #[Endpoint('put', '/server/{id}/species/{speciesId}', 'Update species')]
    public function updateSpecies(string $id, string $speciesId): array {
        $serverId = (int) $id;
        $this->requireGm($serverId);

        $model = new SpeciesModel();
        $row   = $model->findById((int) $speciesId);
        if (!$row) jsonResponse(false, [], ErrorCodes::NOT_FOUND, 404);
        $this->checkOwnsLookup($row, $serverId);

        $model->update((int) $speciesId, $this->body());
        return $model->findById((int) $speciesId);
    }

    #[Endpoint('delete', '/server/{id}/species/{speciesId}', 'Delete species')]
    public function deleteSpecies(string $id, string $speciesId): array {
        $serverId = (int) $id;
        $this->requireGm($serverId);

        $model = new SpeciesModel();
        $row   = $model->findById((int) $speciesId);
        if (!$row) jsonResponse(false, [], ErrorCodes::NOT_FOUND, 404);
        $this->checkOwnsLookup($row, $serverId);

        $model->delete((int) $speciesId);
        return ['message' => 'Deleted.'];
    }

    /* ── CLASSES ── */

    #[Endpoint('post', '/server/{id}/class', 'Create class')]
    public function createClass(string $id): array {
        $serverId = (int) $id;
        $userId   = $this->requireAuth();
        $this->requireGm($serverId);
        $body = $this->body();

        $name = trim((string)($body['name'] ?? ''));
        if ($name === '') jsonResponse(false, [], ErrorCodes::MISSING_PARAMETERS, 400);

        $model = new CharacterClassModel();
        $newId = $model->create($name, $body['description'] ?? null, $serverId, $userId);
        return $model->findById($newId);
    }

    #[Endpoint('put', '/server/{id}/class/{classId}', 'Update class')]
    public function updateClass(string $id, string $classId): array {
        $serverId = (int) $id;
        $this->requireGm($serverId);

        $model = new CharacterClassModel();
        $row   = $model->findById((int) $classId);
        if (!$row) jsonResponse(false, [], ErrorCodes::NOT_FOUND, 404);
        $this->checkOwnsLookup($row, $serverId);

        $model->update((int) $classId, $this->body());
        return $model->findById((int) $classId);
    }

    #[Endpoint('delete', '/server/{id}/class/{classId}', 'Delete class')]
    public function deleteClass(string $id, string $classId): array {
        $serverId = (int) $id;
        $this->requireGm($serverId);

        $model = new CharacterClassModel();
        $row   = $model->findById((int) $classId);
        if (!$row) jsonResponse(false, [], ErrorCodes::NOT_FOUND, 404);
        $this->checkOwnsLookup($row, $serverId);

        $model->delete((int) $classId);
        return ['message' => 'Deleted.'];
    }

    /* ── MÉTIERS ── */

    #[Endpoint('post', '/server/{id}/job', 'Create job')]
    public function createJob(string $id): array {
        $serverId = (int) $id;
        $userId   = $this->requireAuth();
        $this->requireGm($serverId);
        $body = $this->body();

        $name = trim((string)($body['name'] ?? ''));
        if ($name === '') jsonResponse(false, [], ErrorCodes::MISSING_PARAMETERS, 400);

        $model = new CharacterJobModel();
        $newId = $model->create($name, $body['description'] ?? null, $serverId, $userId);
        return $model->findById($newId);
    }

    #[Endpoint('put', '/server/{id}/job/{jobId}', 'Update job')]
    public function updateJob(string $id, string $jobId): array {
        $serverId = (int) $id;
        $this->requireGm($serverId);

        $model = new CharacterJobModel();
        $row   = $model->findById((int) $jobId);
        if (!$row) jsonResponse(false, [], ErrorCodes::NOT_FOUND, 404);
        $this->checkOwnsLookup($row, $serverId);

        $model->update((int) $jobId, $this->body());
        return $model->findById((int) $jobId);
    }

    #[Endpoint('delete', '/server/{id}/job/{jobId}', 'Delete job')]
    public function deleteJob(string $id, string $jobId): array {
        $serverId = (int) $id;
        $this->requireGm($serverId);

        $model = new CharacterJobModel();
        $row   = $model->findById((int) $jobId);
        if (!$row) jsonResponse(false, [], ErrorCodes::NOT_FOUND, 404);
        $this->checkOwnsLookup($row, $serverId);

        $model->delete((int) $jobId);
        return ['message' => 'Deleted.'];
    }

    /* ── ALIGNEMENTS ── */

    #[Endpoint('post', '/server/{id}/alignment', 'Create alignment')]
    public function createAlignment(string $id): array {
        $serverId = (int) $id;
        $userId   = $this->requireAuth();
        $this->requireGm($serverId);
        $body = $this->body();

        $label = trim((string)($body['label'] ?? ''));
        $aura  = in_array($body['aura'] ?? '', ['light', 'neutral', 'dark'], true)
            ? $body['aura'] : 'neutral';

        if ($label === '') jsonResponse(false, [], ErrorCodes::MISSING_PARAMETERS, 400);

        $model = new AlignmentModel();
        $newId = $model->create($label, $aura, $serverId, $userId);
        return $model->findById($newId);
    }

    #[Endpoint('put', '/server/{id}/alignment/{alignId}', 'Update alignment')]
    public function updateAlignment(string $id, string $alignId): array {
        $serverId = (int) $id;
        $this->requireGm($serverId);

        $model = new AlignmentModel();
        $row   = $model->findById((int) $alignId);
        if (!$row) jsonResponse(false, [], ErrorCodes::NOT_FOUND, 404);
        $this->checkOwnsLookup($row, $serverId);

        $model->update((int) $alignId, $this->body());
        return $model->findById((int) $alignId);
    }

    #[Endpoint('delete', '/server/{id}/alignment/{alignId}', 'Delete alignment')]
    public function deleteAlignment(string $id, string $alignId): array {
        $serverId = (int) $id;
        $this->requireGm($serverId);

        $model = new AlignmentModel();
        $row   = $model->findById((int) $alignId);
        if (!$row) jsonResponse(false, [], ErrorCodes::NOT_FOUND, 404);
        $this->checkOwnsLookup($row, $serverId);

        $model->delete((int) $alignId);
        return ['message' => 'Deleted.'];
    }

}
