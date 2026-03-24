<?php

namespace App\Http\Requests;

use App\Support\Reports\ReportFilters;
use Illuminate\Foundation\Http\FormRequest;

class ReportFiltersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isPrivileged = in_array($this->user()?->role, ['admin', 'staff'], true);

        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'community_id' => $isPrivileged ? ['nullable', 'string', 'exists:community,community_id'] : ['nullable'],
            'household_status' => $isPrivileged ? ['nullable', 'in:active,pending,inactive'] : ['nullable'],
            'category_id' => ['nullable', 'integer', 'exists:material_category,category_id'],
        ];
    }

    public function messages(): array
    {
        return [
            'to.after_or_equal' => 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น',
            'community_id.exists' => 'ไม่พบชุมชนที่เลือก',
            'household_status.in' => 'สถานะครัวเรือนไม่ถูกต้อง',
            'category_id.exists' => 'ไม่พบหมวดวัสดุที่เลือก',
        ];
    }

    public function filters(): ReportFilters
    {
        return ReportFilters::fromValidated(
            $this->validated(),
            in_array($this->user()?->role, ['admin', 'staff'], true)
        );
    }
}
