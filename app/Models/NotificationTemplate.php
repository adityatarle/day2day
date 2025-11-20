<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_type_id',
        'channel',
        'subject',
        'body',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the notification type this template belongs to
     */
    public function notificationType(): BelongsTo
    {
        return $this->belongsTo(NotificationType::class);
    }

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by channel
     */
    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Render the template with given data
     */
    public function render(array $data = []): array
    {
        $rendered = [
            'subject' => $this->subject ? $this->replaceVariables($this->subject, $data) : null,
            'body' => $this->replaceVariables($this->body, $data),
        ];

        return $rendered;
    }

    /**
     * Replace template variables with actual data
     */
    private function replaceVariables(string $template, array $data): string
    {
        $variables = $this->variables ?? [];
        
        foreach ($variables as $variable) {
            $placeholder = "{{$variable}}";
            $value = $this->getVariableValue($variable, $data);
            $template = str_replace($placeholder, $value, $template);
        }

        return $template;
    }

    /**
     * Get variable value from data array
     */
    private function getVariableValue(string $variable, array $data): string
    {
        // Handle nested variables like user.name, order.total_amount
        if (str_contains($variable, '.')) {
            $parts = explode('.', $variable);
            $value = $data;
            
            foreach ($parts as $part) {
                if (is_array($value) && isset($value[$part])) {
                    $value = $value[$part];
                } else {
                    return "{{$variable}}"; // Return placeholder if not found
                }
            }
            
            return (string) $value;
        }

        return $data[$variable] ?? "{{$variable}}";
    }

    /**
     * Validate template variables
     */
    public function validateVariables(array $data): array
    {
        $errors = [];
        $variables = $this->variables ?? [];

        foreach ($variables as $variable) {
            if (!$this->hasVariableValue($variable, $data)) {
                $errors[] = "Missing required variable: {$variable}";
            }
        }

        return $errors;
    }

    /**
     * Check if variable has a value in data
     */
    private function hasVariableValue(string $variable, array $data): bool
    {
        if (str_contains($variable, '.')) {
            $parts = explode('.', $variable);
            $value = $data;
            
            foreach ($parts as $part) {
                if (!is_array($value) || !isset($value[$part])) {
                    return false;
                }
                $value = $value[$part];
            }
            
            return true;
        }

        return isset($data[$variable]);
    }

    /**
     * Get template preview with sample data
     */
    public function getPreview(): array
    {
        $sampleData = $this->getSampleData();
        return $this->render($sampleData);
    }

    /**
     * Get sample data for template preview
     */
    private function getSampleData(): array
    {
        $variables = $this->variables ?? [];
        $sampleData = [];

        foreach ($variables as $variable) {
            $sampleData[$variable] = $this->getSampleValue($variable);
        }

        return $sampleData;
    }

    /**
     * Get sample value for a variable
     */
    private function getSampleValue(string $variable): string
    {
        return match($variable) {
            'user.name' => 'John Doe',
            'user.email' => 'john@example.com',
            'order.order_number' => 'ORD-2025-001',
            'order.total_amount' => '₹1,250.00',
            'product.name' => 'Fresh Apples',
            'stock.quantity' => '50',
            'branch.name' => 'Main Branch',
            'payment.amount' => '₹2,500.00',
            'delivery.address' => '123 Main St, City',
            'vendor.name' => 'Fresh Produce Co.',
            default => "Sample {$variable}"
        };
    }
}

