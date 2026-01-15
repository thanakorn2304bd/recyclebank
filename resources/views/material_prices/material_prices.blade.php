<x-layouts.admin title="ราคาของวัสดุ">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">ราคาของ: {{ $material->material_name }}</h3>
    <a class="btn btn-primary" href="{{ route('material-prices.create', ['material_id' => $material->material_id]) }}">+ เพิ่มราคา</a>
  </div>

  <table class="table table-striped bg-white">
    <thead>
      <tr>
        <th style="width:120px;">ราคา</th>
        <th style="width:160px;">เริ่มใช้</th>
        <th style="width:160px;">หมดอายุ</th>
      </tr>
    </thead>
    <tbody>
      @foreach($prices as $p)
        <tr>
          <td>{{ number_format((float)$p->price, 2) }}</td>
          <td>{{ $p->effective_date }}</td>
          <td>{{ $p->expired_date ?? '-' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{ $prices->links() }}
</x-layouts.admin>
