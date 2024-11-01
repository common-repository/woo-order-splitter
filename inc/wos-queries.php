<?php
	global $wpdb;
	$wc_os_recorded_templates_query = "SELECT * FROM `$wpdb->options` WHERE BINARY option_name LIKE UPPER('WC_OS_%')";