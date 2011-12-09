<div id="screen-list">
  <h2>Your screens</h2>

  <?php if(strlen($msg) > 0): ?>
    <div class="msg good-msg">
        <?php print $msg; ?>
    </div>
  <?php endif; ?>

  <div class="listing">
    <?php foreach($rows as $r): ?>
      <div class="list-item"">
        <span class="screen-name"><?php print $r->name; ?></span>
        <?php echo anchor('screen_admin/edit/' . $r->id, 'edit', array('class' => 'edit-link')); ?>
        <?php echo anchor('screen/view/' . $r->id, 'view', array('class' => 'view-link')); ?>
      </div>
    <?php endforeach; ?>
  </div>

  <?php echo anchor('screen_admin/edit', 'add', array('class' => 'add-link')); ?>

</div>