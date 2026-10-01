<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'project_id',
        'invoice_number',
        'client_name',
        'client_email',
        'status',
        'issue_date',
        'due_date',
        'subtotal',
        'tax',
        'total',
        'notes',
        'payment_link',
        'token',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->token)) {
                $invoice->token = Str::random(32);
            }
            
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber($invoice->workspace_id);
            }
        });
    }

    public static function generateInvoiceNumber($workspaceId)
    {
        $latestInvoice = static::where('workspace_id', $workspaceId)
            ->orderBy('id', 'desc')
            ->first();

        if (!$latestInvoice) {
            return 'INV-0001';
        }

        $number = (int) str_replace('INV-', '', $latestInvoice->invoice_number);
        return 'INV-' . str_pad($number + 1, 4, '0', STR_PAD_LEFT);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
