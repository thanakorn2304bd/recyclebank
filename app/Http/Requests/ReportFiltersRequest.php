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
            'household_q' => $isPrivileged ? ['nullable', 'string', 'max:100'] : ['nullable'],
            'household_search_community_id' => $isPrivileged ? ['nullable', 'string', 'exists:community,community_id'] : ['nullable'],
            'household_search_house_no' => $isPrivileged ? ['nullable', 'string', 'max:20'] : ['nullable'],
            'category_id' => ['nullable', 'integer', 'exists:material_category,category_id'],
        ];
    }

    public function messages(): array
    {
        return [
            'to.after_or_equal' => 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น',
            'community_id.exists' => 'ไม่พบชุมชนที่เลือก',
            'household_status.in' => 'สถานะครัวเรือนไม่ถูกต้อง',
            'household_q.max' => 'คำค้นหาครัวเรือนต้องไม่เกิน 100 ตัวอักษร',
            'household_search_community_id.exists' => 'ไม่พบชุมชนสำหรับค้นหาครัวเรือน',
            'household_search_house_no.max' => 'บ้านเลขที่สำหรับค้นหาต้องไม่เกิน 20 ตัวอักษร',
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

    protected function prepareForValidation(): void
    {
        $householdQuery = trim((string) $this->input('household_q', ''));
        $houseNo = trim((string) $this->input('household_search_house_no', ''));

        $this->merge([
            'household_q' => $householdQuery !== '' ? $householdQuery : null,
            'household_search_house_no' => $houseNo !== '' ? $houseNo : null,
        ]);
    }
}
