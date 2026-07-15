<?php
declare(strict_types=1);
namespace App\Services;
/** Portfolio-safe demo adapter. Complete repository automation is omitted. */
final class GitService {
    public function listRepositories(): array {
        return [['name'=>'demo-api','status'=>'active'],['name'=>'demo-frontend','status'=>'maintenance']];
    }
    public function getRepositorySummary(string $repository): array {
        return ['name'=>$repository,'branch'=>'main','last_activity'=>'Portfolio demonstration'];
    }
}
