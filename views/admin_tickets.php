<div class="card reveal">
  <h2>Support Tickets</h2>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?=h($success)?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><?=h($error)?></div>
  <?php endif; ?>

  <table class="table-glass">
    <thead>
      <tr>
        <th>ID</th>
        <th>User/Email</th>
        <th>Subject</th>
        <th>Status</th>
        <th>Created</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tickets as $t): ?>
        <tr>
          <td>#<?=h($t['id'])?></td>
          <td><?=h($t['email'] ?: ($t['user_email'] ?? '—'))?></td>
          <td><?=h($t['subject'])?></td>
          <td><?=h($t['status'])?></td>
          <td><?=h($t['created_at'])?></td>
          <td>
            <form method="post" style="display:inline;">
              <input type="hidden" name="csrf" value="<?=h($csrf)?>">
              <input type="hidden" name="ticket_id" value="<?=h($t['id'])?>">
              <select name="status">
                <option value="open" <?=($t['status']==='open'?'selected':'')?>>Open</option>
                <option value="closed" <?=($t['status']==='closed'?'selected':'')?>>Closed</option>
              </select>
              <button class="btn btn-outline" type="submit">Update</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$tickets): ?>
        <tr><td colspan="6" class="muted">No tickets.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>