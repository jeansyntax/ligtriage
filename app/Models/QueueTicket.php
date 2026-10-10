<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QueueTicket extends Model
{
    public const ACTIVE = ['waiting', 'called'];

    public const PRIORITIES = [
        'emergency' => 'Emergency',
        'urgent' => 'Urgent',
        'routine' => 'Routine',
    ];

    protected $guarded = [];

    protected $casts = [
        'queue_date' => 'date',
        'answers' => 'array',
        'called_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function scopeToday($query)
    {
        return $query->whereDate('queue_date', today());
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', self::ACTIVE);
    }

    # emergency first, then urgent, then routine. same level goes by arrival
    public function scopeOrdered($query)
    {
        return $query
            ->orderByRaw("CASE COALESCE(priority, suggested_priority, 'routine') WHEN 'emergency' THEN 1 WHEN 'urgent' THEN 2 ELSE 3 END")
            ->orderBy('created_at')
            ->orderBy('id');
    }

    public function getEffectivePriorityAttribute(): string
    {
        return $this->priority ?? $this->suggested_priority ?? 'routine';
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->effective_priority] ?? 'Routine';
    }

    public function getIsConfirmedAttribute(): bool
    {
        return $this->priority !== null;
    }

    public function getAgeLabelAttribute(): string
    {
        $group = $this->answers['age_group'] ?? null;

        return $group ? config("triage.age_groups.$group", '-') : '-';
    }

    public function getSymptomSummaryAttribute(): string
    {
        $codes = $this->answers['symptoms'] ?? [];
        $labels = array_map(fn ($c) => config("triage.symptoms.$c", $c), $codes);

        if (! $labels) {
            return $this->answers['notes'] ?? '-';
        }

        $shown = array_slice($labels, 0, 3);
        $extra = count($labels) - count($shown);

        return implode(', ', $shown).($extra > 0 ? " +$extra" : '');
    }

    # minutes between arriving and being called
    public function waitMinutes(): ?int
    {
        if (! $this->called_at) {
            return null;
        }

        return max(0, intdiv($this->called_at->timestamp - $this->created_at->timestamp, 60));
    }

    public static function createForToday(array $attrs): self
    {
        return DB::transaction(function () use ($attrs) {
            $next = (int) static::whereDate('queue_date', today())->lockForUpdate()->max('number') + 1;

            return static::create($attrs + [
                'queue_date' => today(),
                'number' => $next,
                'access_token' => Str::random(40),
            ]);
        });
    }

    # cleans up old tickets so the board only shows today's real queue
    public static function expireStale(): void
    {
        static::whereIn('status', self::ACTIVE)
            ->whereDate('queue_date', '<', today())
            ->update(['status' => 'cancelled', 'finished_at' => now()]);

        static::today()
            ->where('status', 'waiting')
            ->where('source', 'self')
            ->where('created_at', '<', now()->subHours(config('triage.expire_hours', 4)))
            ->update(['status' => 'cancelled', 'finished_at' => now()]);
    }

    public static function avgHandlingMinutes(): int
    {
        $mins = static::today()
            ->where('status', 'done')
            ->whereNotNull('called_at')
            ->whereNotNull('finished_at')
            ->get()
            ->map(fn ($t) => ($t->finished_at->timestamp - $t->called_at->timestamp) / 60);

        if ($mins->count() < 3) {
            return (int) config('triage.default_handling_minutes', 8);
        }

        return max(1, (int) round($mins->avg()));
    }
}
