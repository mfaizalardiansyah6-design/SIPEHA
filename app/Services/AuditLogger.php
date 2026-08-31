<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogger
{
    /**
     * Catat perubahan data ke dalam audit log.
     *
     * Lewati pencatatan bila pengaturan sistem `audit_log` nonaktif.
     */
    public function log(
        string $action,
        string $targetTable,
        ?string $targetId = null,
        ?array $old = null,
        ?array $new = null,
    ): void {
        $sistem = app(Settings::class)->get('sistem', Settings::defaults()['sistem']);

        if (! ($sistem['audit_log'] ?? true)) {
            return;
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'target_table' => $targetTable,
            'target_id' => $targetId,
            'old_value' => $old,
            'new_value' => $new,
        ]);
    }
}
