<x-layouts.admin title="รายละเอียดครัวเรือน">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Household Profile</div>
      <h1 class="rb-page-title">รายละเอียดครัวเรือน</h1>
      <p class="rb-page-subtitle">
        บัญชี {{ $household->account_no }} | ผู้ติดต่อ {{ $household->contact_person }}
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('households.index') }}">กลับ</a>
      @if($isPrivileged)
        <a class="btn btn-outline-primary" href="{{ route('households.documents.index', $household) }}">ดูเอกสาร</a>
        <a class="btn btn-outline-warning" href="{{ route('households.credentials.create', $household) }}">
          {{ $memberAccount ? 'รีเซ็ตรหัสผ่าน' : 'ตั้งรหัสผ่าน' }}
        </a>
        <a class="btn btn-outline-primary" href="{{ route('households.edit', $household) }}">แก้ไข</a>
      @elseif(! $pendingMemberAdditionRequest)
        <a class="btn btn-outline-primary" href="{{ route('households.member-additions.create', $household) }}">
          เพิ่มสมาชิก
        </a>
      @endif
      <a class="btn btn-primary" href="{{ route('transactions.household', $household) }}">ดู statement</a>
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">ยอดคงเหลือ</div>
      <div class="rb-stat-value">{{ number_format((float) $household->total_balance, 2) }}</div>
      <div class="rb-stat-meta">ยอดเงินสะสมล่าสุดของครัวเรือนนี้</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">สมาชิก</div>
      <div class="rb-stat-value">{{ number_format($household->members->count()) }}</div>
      <div class="rb-stat-meta">จำนวนสมาชิกที่ผูกกับครัวเรือน</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">สถานะ</div>
      <div class="rb-stat-value">
        @if($household->active_status === 'active')
          ใช้งาน
        @elseif($household->active_status === 'pending')
          รออนุมัติ
        @else
          ปิด
        @endif
      </div>
      <div class="rb-stat-meta">
        @if($household->reviewed_at)
          พิจารณาล่าสุด {{ $household->reviewed_at->format('d/m/Y H:i') }}
        @else
          สถานะการใช้งานของบัญชีครัวเรือน
        @endif
      </div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">บัญชีเข้าใช้</div>
      <div class="rb-stat-value">{{ $memberAccount ? 'พร้อมใช้' : 'ยังไม่มี' }}</div>
      <div class="rb-stat-meta">
        {{ $memberAccount?->username ? 'ชื่อผู้ใช้ ' . $memberAccount->username : 'ยังไม่ได้ตั้งรหัสผ่านสำหรับสมาชิก' }}
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-5">
      <div class="rb-surface p-4 h-100">
        <div class="rb-section-head">
          <div>
            <h2 class="rb-card-title">ข้อมูลครัวเรือน</h2>
            <p class="rb-card-subtitle">ข้อมูลบัญชี ชุมชน ผู้ติดต่อ และสถานะการใช้งาน</p>
          </div>
        </div>

        <dl class="row rb-detail-list mb-0">
          <dt class="col-sm-5 mb-3">เลขบัญชี</dt>
          <dd class="col-sm-7 mb-3">{{ $household->account_no }}</dd>

          <dt class="col-sm-5 mb-3">ชุมชน</dt>
          <dd class="col-sm-7 mb-3">{{ $household->community?->community_id }} - {{ $household->community?->community_name }}</dd>

          <dt class="col-sm-5 mb-3">บ้านเลขที่</dt>
          <dd class="col-sm-7 mb-3">{{ $household->house_no }}</dd>

          <dt class="col-sm-5 mb-3">หมู่</dt>
          <dd class="col-sm-7 mb-3">{{ $household->village_no ?? '-' }}</dd>

          <dt class="col-sm-5 mb-3">ผู้ติดต่อ</dt>
          <dd class="col-sm-7 mb-3">{{ $household->contact_person }}</dd>

          <dt class="col-sm-5 mb-3">โทรศัพท์</dt>
          <dd class="col-sm-7 mb-3">{{ $household->phone ?? '-' }}</dd>

          <dt class="col-sm-5 mb-3">วันที่สมัคร</dt>
          <dd class="col-sm-7 mb-3">{{ $household->register_date?->format('d/m/Y') ?? '-' }}</dd>

          <dt class="col-sm-5 mb-3">สถานะ</dt>
          <dd class="col-sm-7 mb-3">
            @if($household->active_status === 'active')
              <span class="badge bg-success">ใช้งาน</span>
            @elseif($household->active_status === 'pending')
              <span class="badge bg-warning text-dark">รออนุมัติ</span>
            @else
              <span class="badge bg-secondary">ปิด</span>
            @endif
          </dd>

          <dt class="col-sm-5 mb-3">ผู้พิจารณา</dt>
          <dd class="col-sm-7 mb-3">
            @if($household->reviewedByUser)
              {{ $household->reviewedByUser->staff?->full_name ?? $household->reviewedByUser->username }} ({{ $household->reviewedByUser->username }})
            @else
              -
            @endif
          </dd>

          <dt class="col-sm-5 mb-3">เวลาพิจารณา</dt>
          <dd class="col-sm-7 mb-3">{{ $household->reviewed_at?->format('d/m/Y H:i') ?? '-' }}</dd>

          <dt class="col-sm-5 mb-3">หมายเหตุพิจารณา</dt>
          <dd class="col-sm-7 mb-3">{{ $household->review_notes ?: '-' }}</dd>

          <dt class="col-sm-5 mb-3">สะสม (เดือน)</dt>
          <dd class="col-sm-7 mb-3">{{ $household->accumulated_months }}</dd>

          <dt class="col-sm-5 mb-3">ผู้บันทึก</dt>
          <dd class="col-sm-7 mb-3">
            @if($household->createdByUser)
              {{ $household->createdByUser->username }} ({{ $household->created_by }})
            @else
              {{ $household->created_by ?? '-' }}
            @endif
          </dd>

          <dt class="col-sm-5 mb-3">ชื่อผู้ใช้</dt>
          <dd class="col-sm-7 mb-3">{{ $memberAccount?->username ?? 'ยังไม่ได้ตั้งรหัสผ่าน' }}</dd>

          <dt class="col-sm-5 mb-3">บัญชีเข้าใช้</dt>
          <dd class="col-sm-7 mb-3">
            @if($memberAccount)
              @if($memberAccount->is_active)
                <span class="badge bg-success">พร้อมใช้งาน</span>
              @else
                <span class="badge bg-secondary">ปิดการเข้าใช้งาน</span>
              @endif
            @else
              <span class="text-muted">ยังไม่มีบัญชีเข้าใช้</span>
            @endif
          </dd>

          <dt class="col-sm-5 mb-3">เข้าใช้ล่าสุด</dt>
          <dd class="col-sm-7 mb-3">{{ $memberAccount?->last_login?->format('d/m/Y H:i') ?? '-' }}</dd>

          <dt class="col-sm-5 mb-3">เปลี่ยนรหัสล่าสุด</dt>
          <dd class="col-sm-7 mb-3">{{ $memberAccount?->password_changed_at?->format('d/m/Y H:i') ?? '-' }}</dd>

          <dt class="col-sm-5 mb-3">สถานะรหัสผ่าน</dt>
          <dd class="col-sm-7 mb-3">
            @if($memberAccount?->force_password_reset)
              <span class="badge bg-warning text-dark">ต้องเปลี่ยนรหัสเมื่อเข้าใช้ครั้งถัดไป</span>
            @else
              <span class="badge bg-success">พร้อมใช้งาน</span>
            @endif
          </dd>

          <dt class="col-sm-5 mb-0">อัปเดตล่าสุด</dt>
          <dd class="col-sm-7 mb-0">{{ $household->updated_at?->format('d/m/Y H:i') ?? '-' }}</dd>
        </dl>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="d-grid gap-4">
        @if($isPrivileged)
          <div class="rb-surface p-4">
            <div class="rb-section-head">
              <div>
                <h2 class="rb-card-title">Approval และ Audit</h2>
                <p class="rb-card-subtitle">บันทึกผลการพิจารณาเพื่อเปิดใช้งานหรือปิดใช้งานครัวเรือน พร้อมเก็บผู้พิจารณา เวลา และหมายเหตุไว้ตรวจสอบย้อนหลัง</p>
              </div>
              <span class="rb-chip">
                @if($household->active_status === 'pending')
                  รอพิจารณา
                @elseif($household->active_status === 'active')
                  ใช้งานอยู่
                @else
                  ปิดใช้งาน
                @endif
              </span>
            </div>

            <div class="row g-4">
              <div class="col-lg-5">
                <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                  <div class="small text-uppercase text-muted fw-semibold mb-2">ผลล่าสุด</div>
                  <div class="fw-semibold mb-2">
                    @if($household->active_status === 'pending')
                      ยังไม่ผ่านการพิจารณา
                    @elseif($household->active_status === 'active')
                      อนุมัติให้ใช้งานแล้ว
                    @elseif($household->active_status === 'rejected')
                      ไม่อนุมัติ
                    @else
                      ปิดการใช้งานแล้ว
                    @endif
                  </div>
                  <div class="text-muted small mb-2">ผู้พิจารณา: {{ $household->reviewedByUser?->staff?->full_name ?? $household->reviewedByUser?->username ?? '-' }}</div>
                  <div class="text-muted small mb-2">เวลา: {{ $household->reviewed_at?->format('d/m/Y H:i') ?? '-' }}</div>
                  <div class="text-muted small">หมายเหตุ: {{ $household->review_notes ?: 'ยังไม่มีบันทึกผลการพิจารณา' }}</div>
                </div>
              </div>

              <div class="col-lg-7">
                <form method="POST" action="{{ route('households.review', $household) }}">
                  @csrf
                  @method('PATCH')
                  @php
                    $selectedReviewStatus = old('status', $household->active_status === 'pending' ? 'active' : $household->active_status);
                  @endphp

                  <div class="mb-3">
                    <label class="form-label">กำหนดสถานะบัญชี</label>
                    <div class="rb-review-status-panel" id="household-review-status-panel">
                      <div class="rb-review-status-panel__kicker">ขั้นตอนสำคัญ</div>
                      <div class="rb-review-status-panel__title">เลือกสถานะที่จะใช้กับบัญชีนี้</div>
                      <select class="form-select rb-review-status-select" id="household-review-status" name="status" required>
                        <option value="active" @selected($selectedReviewStatus === 'active')>อนุมัติให้ใช้งาน</option>
                        <option value="rejected" @selected($selectedReviewStatus === 'rejected')>ไม่อนุมัติ</option>
                        <option value="inactive" @selected($selectedReviewStatus === 'inactive')>ปิดการใช้งาน</option>
                      </select>
                      <div class="form-text rb-review-status-panel__help" id="household-review-status-help">เมื่ออนุมัติแล้ว บัญชีสมาชิกของครัวเรือนนี้จะถูกเปิดใช้งานอัตโนมัติถ้ามีการตั้งรหัสผ่านไว้แล้ว</div>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">หมายเหตุการพิจารณา</label>
                    <textarea
                      class="form-control"
                      id="household-review-notes"
                      name="review_notes"
                      rows="4"
                      @required($selectedReviewStatus === 'inactive')
                    >{{ old('review_notes', $household->review_notes) }}</textarea>
                    <div class="form-text" id="household-review-notes-help">
                      การอนุมัติให้ใช้งานสามารถเว้นหมายเหตุได้ แต่หากปิดการใช้งานต้องระบุเหตุผลหรือหลักฐานที่ใช้พิจารณา
                    </div>
                  </div>

                  <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-primary">บันทึกผลการพิจารณา</button>
                    <a class="btn btn-outline-primary" href="{{ route('households.documents.index', $household) }}">
                      ดูเอกสาร
                    </a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        @endif

        <div class="rb-surface p-4">
          <div class="rb-section-head">
            <div>
              <h2 class="rb-card-title">สมาชิกในครัวเรือน</h2>
              <p class="rb-card-subtitle">ดูรายชื่อสมาชิก ความสัมพันธ์ และผู้เป็นหัวหน้าครัวเรือน</p>
            </div>
            <span class="rb-chip">{{ $household->members->count() }} คน</span>
          </div>

          @if($household->members->isEmpty())
            <div class="rb-empty-state">ยังไม่มีสมาชิกในครัวเรือนนี้</div>
          @else
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0" data-sortable-table>
                <thead>
                  <tr>
                    <th style="width:50px;" data-sort-type="number">#</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th style="width:160px;">เลขบัตรประชาชน</th>
                    <th style="width:140px;">ความสัมพันธ์</th>
                    <th style="width:90px;">หัวหน้า</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($household->members as $index => $member)
                    <tr>
                      <td>{{ $index + 1 }}</td>
                      <td>{{ $member->full_name }}</td>
                      <td>{{ $member->masked_id_card ?? '-' }}</td>
                      <td>{{ $member->relation ?? '-' }}</td>
                      <td>
                        @if($member->is_head)
                          <span class="badge bg-success">หัวหน้า</span>
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>

        @include('households.partials.member-addition-requests')
      </div>
    </div>
  </div>

  @if($isPrivileged)
    <style>
      .rb-review-status-panel {
        border: 1px solid rgba(23, 169, 122, 0.24);
        border-radius: 1.25rem;
        background:
          linear-gradient(135deg, rgba(238, 249, 243, 0.98) 0%, rgba(255, 255, 255, 0.96) 55%),
          linear-gradient(180deg, rgba(52, 211, 153, 0.08) 0%, rgba(255, 255, 255, 0) 100%);
        box-shadow: 0 18px 34px rgba(15, 109, 74, 0.08);
        padding: 1rem;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
      }

      .rb-review-status-panel--active {
        border-color: rgba(16, 185, 129, 0.32);
        box-shadow: 0 18px 36px rgba(16, 185, 129, 0.12);
      }

      .rb-review-status-panel--inactive {
        border-color: rgba(234, 88, 12, 0.28);
        background:
          linear-gradient(135deg, rgba(255, 247, 237, 0.98) 0%, rgba(255, 255, 255, 0.96) 58%),
          linear-gradient(180deg, rgba(251, 146, 60, 0.08) 0%, rgba(255, 255, 255, 0) 100%);
        box-shadow: 0 18px 36px rgba(234, 88, 12, 0.12);
      }

      .rb-review-status-panel__kicker {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        background: rgba(15, 109, 74, 0.1);
        color: #0f6d4a;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        padding: 0.35rem 0.7rem;
        text-transform: uppercase;
      }

      .rb-review-status-panel__title {
        margin-top: 0.7rem;
        margin-bottom: 0.85rem;
        color: #0d5134;
        font-size: 1.02rem;
        font-weight: 700;
      }

      .rb-review-status-select {
        min-height: 58px;
        border-width: 2px;
        font-size: 1.05rem;
        font-weight: 600;
      }

      .rb-review-status-panel__help {
        margin-top: 0.75rem;
        font-size: 0.88rem;
      }

      .rb-review-status-panel--inactive .rb-review-status-panel__kicker {
        background: rgba(234, 88, 12, 0.12);
        color: #c2410c;
      }

      .rb-review-status-panel--inactive .rb-review-status-panel__title {
        color: #9a3412;
      }
    </style>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const statusField = document.getElementById('household-review-status');
        const statusPanel = document.getElementById('household-review-status-panel');
        const statusHelp = document.getElementById('household-review-status-help');
        const reviewNotesField = document.getElementById('household-review-notes');
        const reviewNotesHelp = document.getElementById('household-review-notes-help');

        if (! statusField || ! statusPanel || ! statusHelp || ! reviewNotesField || ! reviewNotesHelp) {
          return;
        }

        const statusHelpTexts = {
          active: 'เมื่ออนุมัติแล้ว บัญชีสมาชิกของครัวเรือนนี้จะถูกเปิดใช้งานอัตโนมัติถ้ามีการตั้งรหัสผ่านไว้แล้ว',
          rejected: 'ใช้เมื่อเอกสารไม่ครบหรือข้อมูลไม่ถูกต้อง ต้องระบุเหตุผลประกอบ',
          inactive: 'ใช้เมื่อจำเป็นต้องระงับการเข้าใช้งานบัญชี และควรระบุเหตุผลประกอบไว้ด้านล่าง',
        };
        const reviewNotesHelpTexts = {
          active: 'การอนุมัติให้ใช้งานสามารถเว้นหมายเหตุได้ แต่หากปิดการใช้งานต้องระบุเหตุผลหรือหลักฐานที่ใช้พิจารณา',
          rejected: 'กรณีไม่อนุมัติ ต้องระบุเหตุผล เช่น เอกสารไม่ครบ ข้อมูลไม่ตรง หรือไม่ผ่านเงื่อนไข',
          inactive: 'กรณีปิดการใช้งาน ต้องระบุเหตุผลหรือหลักฐานที่ใช้พิจารณา เช่น ปิดใช้งานตามคำร้อง เอกสารไม่ครบ หรือข้อมูลไม่ตรงกัน',
        };

        function syncReviewNotesRequirement() {
          const requiresReviewNotes = statusField.value === 'inactive' || statusField.value === 'rejected';

          statusPanel.classList.toggle('rb-review-status-panel--active', ! requiresReviewNotes);
          statusPanel.classList.toggle('rb-review-status-panel--inactive', requiresReviewNotes);
          statusHelp.textContent = statusHelpTexts[statusField.value] ?? statusHelpTexts.active;

          if (requiresReviewNotes) {
            reviewNotesField.setAttribute('required', 'required');
            reviewNotesField.setAttribute('aria-required', 'true');
          } else {
            reviewNotesField.removeAttribute('required');
            reviewNotesField.removeAttribute('aria-required');
          }

          reviewNotesHelp.textContent = reviewNotesHelpTexts[statusField.value] ?? reviewNotesHelpTexts.active;
        }

        statusField.addEventListener('change', syncReviewNotesRequirement);
        syncReviewNotesRequirement();
      });
    </script>
  @endif
</x-layouts.admin>
