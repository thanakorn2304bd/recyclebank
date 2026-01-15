<x-layouts.admin title="ราคา">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">ราคาวัสดุ</h3>
    <a class="btn btn-primary" href="{{ route('material-prices.create') }}">+ เพิ่มราคา</a>
  </div>

  <form class="row g-2 mb-3">
    <div class="col-md-6">
      <select class="form-select" name="material_id">
        <option value="">ทุกวัสดุ</option>
        @foreach($materials as $m)
          <option value="{{ $m->material_id }}" @selected((string)$materialId === (string)$m->material_id)>
            {{ $m->material_name }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3">
      <button class="btn btn-outline-primary w-100">กรอง</button>
    </div>
    <div class="col-md-3">
      <a class="btn btn-outline-secondary w-100" href="{{ route('material-prices.index') }}">ล้าง</a>
    </div>
  </form>

  <table class="table table-striped bg-white">
    <thead>
      <tr>
        <th>วัสดุ</th>
        <th style="width:120px;">ราคา</th>
        <th style="width:150px;">เริ่มใช้</th>
        <th style="width:150px;">หมดอายุ</th>
        <th style="width:120px;"></th>
      </tr>
    </thead>
    <tbody>
      @foreach($prices as $p)
        <tr>
          <td>{{ $p->material?->material_name }}</td>
          <td>{{ number_format((float)$p->price, 2) }}</td>
          <td>{{ $p->effective_date }}</td>
          <td>{{ $p->expired_date ?? '-' }}</td>
          <td class="text-end">
            <form class="d-inline" method="POST" action="{{ route('material-prices.destroy', $p) }}"
                  onsubmit="return confirm('ลบราคานี้?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger">ลบ</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{ $prices->links() }}
</x-layouts.admin>
