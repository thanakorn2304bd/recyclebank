<x-layouts.admin title="หมวดวัสดุ">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">หมวดวัสดุ</h3>
    <a class="btn btn-primary" href="{{ route('material-categories.create') }}">+ เพิ่มหมวด</a>
  </div>

  <table class="table table-striped bg-white">
    <thead>
      <tr>
        <th style="width:120px;">ID</th>
        <th>ชื่อหมวด</th>
        <th style="width:200px;"></th>
      </tr>
    </thead>
    <tbody>
      @foreach($categories as $c)
        <tr>
          <td>{{ $c->category_id }}</td>
          <td>{{ $c->category_name }}</td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('material-categories.edit', $c) }}">แก้ไข</a>
            <form class="d-inline" method="POST" action="{{ route('material-categories.destroy', $c) }}"
                  onsubmit="return confirm('ลบหมวดนี้?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger">ลบ</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{ $categories->links() }}
</x-layouts.admin>
