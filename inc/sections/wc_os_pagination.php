<?php
  $group_span = 100;
  $control_section = ($total_pages?ceil($total_pages/$group_span):0);
  $active_group = ($current > 0 ? ceil($current / $group_span) : 1);	


?>



<ul class="wos_pagination">

<?php 

  if($control_section > 1):

?>
    <li class="wos_controls" data-total_group='<?php echo esc_attr($control_section); ?>'>

      <i class="fa fa-caret-up wos_up" data-control="up" title="<?php _e('Click view next group', 'woo-order-splitter') ?>"></i>
      <i class="fa fa-circle wos_toggle" data-control="toggle" title="<?php _e('Click to Show/Hide all pages list', 'woo-order-splitter') ?>"></i>
      <i class="fa fa-caret-down wos_down" data-control="down" title="<?php _e('Click view previous group', 'woo-order-splitter') ?>"></i>

    </li>

<?php
  endif;
?>
<li class="wos-first"><a data-num="1"><?php _e('First', 'woo-order-splitter'); ?></a></li>
<li class="wos-prev" <?php echo ($current>1?'':'style="display:none"'); ?>><a data-num="<?php echo ($current-1>0?$current-1:$current); ?>"><?php _e('Prev', 'woo-order-splitter'); ?></a></li>
<?php
  $single_group_counter = 1;
  $total_group_counter = 1;
  for($i=1; $i<=$total_pages; $i++):

    $active_class = $active_group == $total_group_counter ? 'wos_active_group' : 'wos_inactive_group';
  
  ?>    
<?php
  if($total_pages<=20 || (($i >= 1 && $i <= $radius) || ($i > $current - $radius && $i < $current + $radius) || ($i <= $total_pages && $i > $total_pages - $radius))){
?>
	<li data-num="<?php echo esc_attr($i); ?>" data-group="<?php echo esc_attr($total_group_counter); ?>" class="wos_single  <?php echo esc_attr($active_class); ?> group_<?php echo esc_attr($total_group_counter); ?> <?php echo ($current==$i?'wos_current':''); ?>"><a class="nums" data-num="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></a></li>
<?php
  }
  elseif($i == $current - $radius || $i == $current + $radius) {
?>
	<li class="<?php echo ($current==$i?'wos_current':''); ?>">...</li>
<?php
  }

  if($single_group_counter == $group_span){
    $single_group_counter = 1;
    $total_group_counter++;
  }else{
    $single_group_counter++;
  }

?>  
<?php endfor; ?>  
<li class="wos-last"><a data-num="<?php echo esc_attr($total_pages); ?>"><?php _e('Last', 'woo-order-splitter'); ?></a></li>
<li class="wos-next" <?php echo (($current<$total_pages)?'':'style="display:none"'); ?>><a data-num="<?php echo (($current+1)<=$total_pages?$current+1:$total_pages); ?>"><?php _e('Next', 'woo-order-splitter'); ?></a></li>
</ul>