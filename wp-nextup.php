<?php
/*
Plugin Name:  Next Up Analytics Exporter
Plugin URI:   http://nextup.work
Description:  Plugin to export stats about what users logged in within the last week.
Version:      1
Author:       Chase McClure
Author URI:   https://setti.io
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wporg
Domain Path:  /languages
*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

class WPNextUp {

	public function __construct() {
		add_action('admin_menu', array($this, 'add_admin_item'));
		add_action('admin_footer', array($this, 'add_click_javascript'));
		add_action('init', array($this, 'run'));
	}


	public function add_admin_item() {
		add_menu_page('NextUp User CSV', 'NextUp User CSV', 'manage_options', 'nextup_download_csv', array($this, 'render_admin_page'));
	}



	public function add_click_javascript() { ?>
		<script type="text/javascript" >
			jQuery(document).ready(function($) {
				jQuery('#download-csv').click(function() {
					var url = window.location.href;    
					if (url.indexOf('?') > -1) {
   						url += '&download_csv=1'
					} else {
   						url += '?download_csv=1'
					}
					window.location.href = url;
				});
			});
		</script>
	<?php
	}



	public function render_admin_page() {
		echo '<div class="wrap">';
		echo '	<h2>NextUp User Analytics Exporter</h2>';
		echo '	<p>Here is a description of what the button does.</p>';
		echo '	<button id="download-csv">Download CSV</button>';
		echo '</div>';
	}



	public function run() {
		if (!current_user_can('administrator')) {
			return;
		}

		if(isset($_GET['download_csv'])) {
			$csv = $this->generate_csv();

			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private", false);
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"nextup.csv\";" );
			header("Content-Transfer-Encoding: binary");

			echo $csv;
			exit;
		}
	}

	public function generate_csv() {
		$csv_output = "Name,Email";
		$csv_output .= "\n";
		$user_query = new WP_User_Query(array(
			'role' => 'Administrator'
		));

		if (!empty($user_query->get_results())) {
			foreach ($user_query->get_results() as $user) {
				$csv_output .= $user->display_name.",".$user->user_email;
				$csv_output .= "\n";
			}
		} else {
			error_log('No Users');
		}

		return $csv_output;
	}
}

$plugin = new WPNextUp();
?>