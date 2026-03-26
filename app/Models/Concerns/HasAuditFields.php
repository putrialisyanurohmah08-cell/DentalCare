<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasAuditFields
{
    protected static function bootHasAuditFields(): void
    {
        static::addGlobalScope('not_deleted', function (Builder $query): void {
            $query->where($query->qualifyColumn('IsDeleted'), 0);
        });

        static::creating(function ($model): void {
            $now = now();
            $auditUser = $model->resolveAuditUser();

            $model->CompanyCode ??= config('clinic.company_code');
            $model->Status ??= 1;
            $model->IsDeleted ??= 0;
            $model->CreatedBy ??= $auditUser;
            $model->CreatedDate ??= $now;
            $model->LastUpdatedBy ??= $auditUser;
            $model->LastUpdatedDate ??= $now;
        });

        static::updating(function ($model): void {
            $model->LastUpdatedBy = $model->resolveAuditUser();
            $model->LastUpdatedDate = now();
        });
    }

    public function scopeWithDeleted(Builder $query): Builder
    {
        return $query->withoutGlobalScope('not_deleted');
    }

    public function scopeOnlyDeleted(Builder $query): Builder
    {
        return $query->withoutGlobalScope('not_deleted')->where($this->qualifyColumn('IsDeleted'), 1);
    }

    public function delete(): ?bool
    {
        if (! $this->exists) {
            return false;
        }

        $this->IsDeleted = 1;
        $this->LastUpdatedBy = $this->resolveAuditUser();
        $this->LastUpdatedDate = now();

        return $this->saveQuietly();
    }

    public function restoreRecord(): bool
    {
        $this->IsDeleted = 0;
        $this->LastUpdatedBy = $this->resolveAuditUser();
        $this->LastUpdatedDate = now();

        return $this->saveQuietly();
    }

    protected function resolveAuditUser(): string
    {
        return Auth::user()?->email ?? 'system';
    }

    public function usesTimestamps(): bool
    {
        return false;
    }

    public function getCreatedAtColumn(): ?string
    {
        return 'CreatedDate';
    }

    public function getUpdatedAtColumn(): ?string
    {
        return 'LastUpdatedDate';
    }
}
