<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait Archivable
{
    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function archive(?int $userId = null): bool
    {
        $this->forceFill(['archived_by' => $userId ?? auth()->id()])->save();

        return (bool) $this->delete();
    }

    public function restoreFromArchive(): bool
    {
        $restored = $this->restore();

        if ($restored) {
            $this->forceFill(['archived_by' => null])->save();
        }

        return $restored;
    }
}
