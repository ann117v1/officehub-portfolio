<?php
declare(strict_types=1);
namespace App\Services;
/** Demo-only integration boundary. External API synchronization is omitted. */
final class TrelloService {
    public function getDemoBoard(): array { return ['name'=>'Portfolio Demo Board','lists'=>['Backlog','In progress','Completed']]; }
}
