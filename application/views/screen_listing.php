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
        <?php echo anchor('screen/index/' . $r->id, 'view', array('class' => 'view-link')); ?>
        <div class="last-checkin"><span>Last checkin:</span> 
          <?php if($r->last_checkin > 0): ?>
            <?php print date('D, M j, Y, g:i:s a',strtotime($r->last_checkin)); ?>
          <?php else: ?>
            Never
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php echo anchor('screen_admin/edit', 'add', array('class' => 'add-link')); ?>

</div>