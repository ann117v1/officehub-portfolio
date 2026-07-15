<?php
declare(strict_types=1);
namespace App\Services;
/** Safe preview only. No SMTP implementation is included. */
final class MailService {
    public function preview(string $recipient, string $subject): array {
        return ['recipient'=>$recipient,'subject'=>$subject,'delivery'=>'disabled-in-showcase'];
    }
}
