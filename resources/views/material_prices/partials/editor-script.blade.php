<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('rbBulkPriceForm');

    if (! form) {
      return;
    }

    const rows = Array.from(form.querySelectorAll('[data-material-row]'));
    const summary = document.getElementById('rbDirtyPriceSummary');
    const submitButton = document.getElementById('rbSubmitPriceEditor');
    const resetButtons = [
      document.getElementById('rbResetPriceEditor'),
      document.getElementById('rbResetPriceEditorBottom'),
    ].filter(Boolean);
    const totalRows = rows.length;

    function rowInputs(row) {
      return Array.from(row.querySelectorAll('input[type="number"]'));
    }

    function isDirty(row) {
      return rowInputs(row).some(function (input) {
        return String(input.value ?? '') !== String(input.dataset.initialValue ?? '');
      });
    }

    function refreshDirtyState() {
      let dirtyCount = 0;

      rows.forEach(function (row) {
        const dirty = isDirty(row);

        row.classList.toggle('rb-price-editor-row-dirty', dirty);

        if (dirty) {
          dirtyCount += 1;
        }
      });

      if (summary) {
        summary.textContent = dirtyCount > 0
          ? 'แก้จากค่าตั้งต้น ' + dirtyCount + ' จาก ' + totalRows + ' รายการ'
          : 'ยังไม่ได้แก้จากค่าตั้งต้น ระบบจะเผยแพร่ตามค่าที่แสดง';
      }

      if (submitButton) {
        submitButton.textContent = dirtyCount > 0
          ? 'เผยแพร่เดือนนี้ (แก้ ' + dirtyCount + ' รายการ)'
          : 'เผยแพร่ชุดราคาเดือนนี้';
      }
    }

    function resetChanges() {
      rows.forEach(function (row) {
        rowInputs(row).forEach(function (input) {
          input.value = input.dataset.initialValue ?? '';
        });
      });

      refreshDirtyState();
    }

    rows.forEach(function (row) {
      rowInputs(row).forEach(function (input) {
        input.addEventListener('input', refreshDirtyState);
        input.addEventListener('change', refreshDirtyState);
      });
    });

    resetButtons.forEach(function (button) {
      button.addEventListener('click', resetChanges);
    });

    refreshDirtyState();
  });
</script>
