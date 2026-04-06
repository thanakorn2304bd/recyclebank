<x-layouts.admin title="เอกสารสมาชิกครัวเรือน">
  <div class="rb-page-header">
    <div>
      <div class="rb-page-kicker">Household Documents</div>
      <h1 class="rb-page-title">เอกสารสมาชิกครัวเรือน</h1>
      <p class="rb-page-subtitle">
        บัญชี {{ $household->account_no }} | ดูเอกสารของสมาชิกในครัวเรือนทั้งหมด และแยกส่วนสมาชิกที่ยังรอดำเนินการให้อ่านง่าย
      </p>
    </div>
    <div class="rb-page-actions">
      <a class="btn btn-outline-secondary" href="{{ route('households.show', $household) }}">กลับไปหน้ารายละเอียด</a>
      @if(($documentSummary['open_request_count'] ?? 0) > 0)
        <a class="btn btn-outline-primary" href="#household-pending-document-groups">ดูสมาชิกที่รอดำเนินการ</a>
      @endif
    </div>
  </div>

  <div class="rb-stat-grid">
    <div class="rb-stat-card">
      <div class="rb-stat-label">สมาชิกในครัวเรือน</div>
      <div class="rb-stat-value">{{ number_format($documentSummary['member_count'] ?? 0) }}</div>
      <div class="rb-stat-meta">จำนวนสมาชิกที่อยู่ในบัญชีครัวเรือนนี้ตอนนี้</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">เอกสารทั้งหมด</div>
      <div class="rb-stat-value">{{ number_format($documentSummary['document_count'] ?? 0) }}</div>
      <div class="rb-stat-meta">รวมเอกสารสมัครครั้งแรกและเอกสารคำขอเพิ่มสมาชิกทุกชุด</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">คำขอที่ต้องติดตาม</div>
      <div class="rb-stat-value">{{ number_format($documentSummary['open_request_count'] ?? 0) }}</div>
      <div class="rb-stat-meta">คำขอเพิ่มสมาชิกที่ยังรอตรวจสอบหรือถูกตีกลับ</div>
    </div>
    <div class="rb-stat-card">
      <div class="rb-stat-label">สมาชิกที่รอดำเนินการ</div>
      <div class="rb-stat-value">{{ number_format($documentSummary['open_request_member_count'] ?? 0) }}</div>
      <div class="rb-stat-meta">สมาชิกใหม่ที่ยังไม่ถูกรวมเข้าเป็นสมาชิกปัจจุบัน</div>
    </div>
  </div>

  <div class="alert alert-info mb-4">
    หน้านี้จัดเอกสารเป็นรายสมาชิกโดยตรง เพื่อให้เจ้าหน้าที่เปิดดูทะเบียนบ้านและบัตรประชาชนของแต่ละคนได้จากที่เดียว ไม่ต้องไล่กลับไปดูหลายกล่องในหน้ารายละเอียด
  </div>

  <div class="rb-surface p-4 mb-4" id="household-current-member-documents">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">สมาชิกในครัวเรือน</h2>
        <p class="rb-card-subtitle">แสดงรายชื่อสมาชิกที่อยู่ในครัวเรือนปัจจุบัน พร้อมเอกสารของแต่ละคนแบบอ่านง่ายทีละการ์ด</p>
      </div>
      <span class="rb-chip">{{ count($currentMemberDocumentCards) }} คน</span>
    </div>

    @if($currentMemberDocumentCards === [])
      <div class="rb-empty-state">ยังไม่มีสมาชิกให้แสดงในครัวเรือนนี้</div>
    @else
      <div class="row g-4">
        @foreach($currentMemberDocumentCards as $member)
          <div class="col-xl-6">
            <section class="rb-doc-member-card h-100">
              <div class="rb-doc-member-card__head">
                <div>
                  <div class="rb-doc-member-card__title">{{ $member['title'] }}</div>
                  @if($member['meta'] !== '')
                    <div class="rb-doc-member-card__meta">{{ $member['meta'] }}</div>
                  @endif
                </div>
                @if($member['is_head'])
                  <span class="badge text-bg-success">หัวหน้า</span>
                @endif
              </div>

              <div class="d-grid gap-3 mt-3">
                @foreach($member['documents'] as $document)
                  <div class="rb-doc-item">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                      <div>
                        <div class="rb-doc-item__label">{{ $document['label'] }}</div>
                        @if($document['document'])
                          <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                            <span class="badge text-bg-success">ได้รับเอกสารแล้ว</span>
                            <span class="text-muted small">อัปโหลดเมื่อ {{ $document['uploaded_at'] ?? '-' }}</span>
                          </div>
                          @if($document['source_label'])
                            <div class="text-muted small mt-2">ที่มา: {{ $document['source_label'] }}</div>
                          @endif
                        @else
                          <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                            <span class="badge text-bg-secondary">ยังไม่มีไฟล์</span>
                            <span class="text-muted small">ยังไม่พบเอกสารประเภทนี้ในระบบ</span>
                          </div>
                        @endif
                      </div>

                      @if($document['document'])
                        <div class="d-flex flex-wrap gap-2">
                          <a class="btn btn-sm btn-outline-primary" href="{{ $document['view_url'] }}" target="_blank" rel="noopener noreferrer">ดูไฟล์</a>
                          <a class="btn btn-sm btn-outline-secondary" href="{{ $document['download_url'] }}">ดาวน์โหลด</a>
                        </div>
                      @endif
                    </div>
                  </div>
                @endforeach
              </div>
            </section>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  <div class="rb-surface p-4" id="household-pending-document-groups">
    <div class="rb-section-head">
      <div>
        <h2 class="rb-card-title">สมาชิกที่รอดำเนินการ</h2>
        <p class="rb-card-subtitle">ใช้ส่วนนี้เพื่อตรวจเอกสารของสมาชิกใหม่ที่ยังรออนุมัติ หรือคำขอที่ถูกตีกลับแล้วต้องติดตามต่อ</p>
      </div>
      <span class="rb-chip">{{ count($openRequestDocumentGroups) }} คำขอ</span>
    </div>

    @if($openRequestDocumentGroups === [])
      <div class="rb-empty-state">ตอนนี้ไม่มีคำขอเพิ่มสมาชิกที่ต้องติดตามเพิ่มเติม</div>
    @else
      <div class="d-grid gap-4">
        @foreach($openRequestDocumentGroups as $group)
          <section class="rb-pending-group">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                  <h3 class="h5 mb-0">{{ $group['title'] }}</h3>
                  <span class="badge {{ $group['badge_class'] }}">{{ $group['badge_label'] }}</span>
                </div>
                <div class="text-muted small mt-2">{{ $group['subtitle'] }}</div>
                @if($group['review_notes'])
                  <div class="text-muted small mt-1">หมายเหตุ: {{ $group['review_notes'] }}</div>
                @endif
              </div>
              <div class="text-muted small">{{ count($group['members']) }} คน</div>
            </div>

            <div class="row g-3 mt-1">
              @foreach($group['members'] as $member)
                <div class="col-xl-6">
                  <section class="rb-doc-member-card h-100">
                    <div class="rb-doc-member-card__head">
                      <div>
                        <div class="rb-doc-member-card__title">{{ $member['title'] }}</div>
                        @if($member['meta'] !== '')
                          <div class="rb-doc-member-card__meta">{{ $member['meta'] }}</div>
                        @endif
                      </div>
                    </div>

                    <div class="d-grid gap-3 mt-3">
                      @foreach($member['documents'] as $document)
                        <div class="rb-doc-item">
                          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                              <div class="rb-doc-item__label">{{ $document['label'] }}</div>
                              @if($document['document'])
                                <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                  <span class="badge text-bg-success">ได้รับเอกสารแล้ว</span>
                                  <span class="text-muted small">อัปโหลดเมื่อ {{ $document['uploaded_at'] ?? '-' }}</span>
                                </div>
                                @if($document['source_label'])
                                  <div class="text-muted small mt-2">ที่มา: {{ $document['source_label'] }}</div>
                                @endif
                              @else
                                <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                  <span class="badge text-bg-secondary">ยังไม่มีไฟล์</span>
                                  <span class="text-muted small">ยังไม่พบเอกสารประเภทนี้ในระบบ</span>
                                </div>
                              @endif
                            </div>

                            @if($document['document'])
                              <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="{{ $document['view_url'] }}" target="_blank" rel="noopener noreferrer">ดูไฟล์</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ $document['download_url'] }}">ดาวน์โหลด</a>
                              </div>
                            @endif
                          </div>
                        </div>
                      @endforeach
                    </div>
                  </section>
                </div>
              @endforeach
            </div>
          </section>
        @endforeach
      </div>
    @endif
  </div>

  <style>
    .rb-doc-member-card {
      height: 100%;
      border: 1px solid var(--rb-green-200);
      border-radius: 1.2rem;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(245, 251, 247, 0.98) 100%);
      box-shadow: var(--rb-shadow-soft);
      padding: 1.15rem;
    }

    .rb-doc-member-card__head {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: start;
      gap: 0.75rem;
    }

    .rb-doc-member-card__title {
      color: #0d5134;
      font-size: 1.05rem;
      font-weight: 700;
      line-height: 1.45;
    }

    .rb-doc-member-card__meta {
      margin-top: 0.35rem;
      color: var(--rb-text-soft);
      font-size: 0.9rem;
      line-height: 1.6;
    }

    .rb-doc-item {
      border: 1px solid var(--rb-green-200);
      border-radius: 1rem;
      background: rgba(255, 255, 255, 0.96);
      padding: 1rem;
    }

    .rb-doc-item__label {
      color: #173c2d;
      font-size: 0.96rem;
      font-weight: 700;
    }

    .rb-pending-group {
      border: 1px solid rgba(250, 204, 21, 0.26);
      border-radius: 1.2rem;
      background:
        linear-gradient(180deg, rgba(255, 251, 235, 0.98) 0%, rgba(255, 255, 255, 0.96) 100%);
      box-shadow: var(--rb-shadow-soft);
      padding: 1rem;
    }
  </style>
</x-layouts.admin>
