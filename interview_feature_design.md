# Interview Feature Schema Design (Option A)

This document outlines the database schema updates required to implement the interview selection stage for vacancy applications.

## 1. Schema Changes (`vacancy_applications` Table)

We will modify the existing `vacancy_applications` table to support the new status and store scheduling/link details.

### Status Enum Update
* **Current status**: `['pending', 'accepted', 'rejected']`
* **New status**: `['pending', 'interviewing', 'accepted', 'rejected']`

### New Columns
| Column Name | Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `interview_link` | `string(2083)` | `nullable` | URL for Zoom, Google Meet, or WhatsApp group. |
| `interview_scheduled_at` | `timestamp` | `nullable` | Date and time for the interview. |
| `interview_notes` | `text` | `nullable` | Special instructions or messages from organizers. |

---

## 2. Migration Definition

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacancy_applications', function (Blueprint $table) {
            // Update enum values
            $table->enum('status', ['pending', 'interviewing', 'accepted', 'rejected'])
                  ->default('pending')
                  ->change();

            // Add interview details
            $table->string('interview_link', 2083)->nullable()->after('status');
            $table->timestamp('interview_scheduled_at')->nullable()->after('interview_link');
            $table->text('interview_notes')->nullable()->after('interview_scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::table('vacancy_applications', function (Blueprint $table) {
            $table->dropColumn(['interview_link', 'interview_scheduled_at', 'interview_notes']);
            
            // Revert enum values
            $table->enum('status', ['pending', 'accepted', 'rejected'])
                  ->default('pending')
                  ->change();
        });
    }
};
```
