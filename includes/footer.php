  </main><!-- /.main-content -->
</div><!-- /.layout -->

<script>
/* ── Sidebar toggle (mobile) ───────────────────── */
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  sidebar.classList.toggle('open');
  overlay.classList.toggle('visible');
}

/* ── Stock adjuster (inventory.php) ────────────── */
function updateStock(id, change) {
  fetch('update_stock.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id, change })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      // Update the stock number in the DOM
      const btn    = event.target.closest('.stock-adjuster');
      const span   = btn ? btn.querySelector('span') : null;
      if (span) span.textContent = data.new_stock;

      // Update the status badge
      const row    = btn ? btn.closest('tr') : null;
      const badge  = row ? row.querySelector('.badge') : null;
      if (badge) {
        badge.className = 'badge ' + data.status_class;
        badge.textContent = data.status;
      }
    } else {
      alert('Error: ' + (data.error || 'Could not update stock'));
    }
  })
  .catch(() => alert('Network error. Please try again.'));
}

/* ── Delete confirmation (inventory.php) ────────── */
function deleteItem(id, name) {
  if (confirm('Delete "' + name + '"?\n\nThis action cannot be undone.')) {
    window.location.href = 'delete_item.php?id=' + id;
  }
}
</script>
</body>
</html>
