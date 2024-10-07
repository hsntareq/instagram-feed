<?php

namespace GGInstagram;

class Plugin {
    public function __construct() {
        // Register hooks
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_ajax_gg_save_instagram_data', [$this, 'gg_save_instagram_data']);
        add_action('wp_ajax_nopriv_gg_save_instagram_data', [$this, 'gg_save_instagram_data']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('init', [$this, 'handle_redirect']);
		// add scripts for gg-settings page admin head
		// add_action('admin_head-toplevel_page_gg-instagram', [$this, 'add_scripts']);

		// add html after body of gg-settings page admin
		// add_action('admin_footer-toplevel_page_gg-instagram', [$this, 'add_html']);
		add_action('admin_init', [$this, 'gg_instagram_redirect_fix']);
    }


	function gg_instagram_redirect_fix() {
		if (isset($_GET['code']) && !isset($_GET['page'])) {
			// The code is present but the 'page' parameter is missing
			$redirect_url = add_query_arg([
				'page' => 'gg-instagram-redirect',
				'code' => sanitize_text_field($_GET['code'])
			], admin_url('admin.php'));

			wp_safe_redirect($redirect_url);
			exit;
		}
	}

	public function add_html() {
		?>
		<div
			class="fb-like"
			data-share="true"
			data-width="450"
			data-show-faces="true">
		</div>
		<?php
	}

	public function add_scripts() {
		?>
		<script>
			window.fbAsyncInit = function() {
				FB.init({
				appId      : '509411041455347',
				xfbml      : true,
				version    : 'v21.0'
				});
				FB.AppEvents.logPageView();
			};

			(function(d, s, id){
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) {return;}
				js = d.createElement(s); js.id = id;
				js.src = "https://connect.facebook.net/en_US/sdk.js";
				fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));
		</script>
		<?php
	}

    public function run() {
        // Add any initialization code here
    }

    public function add_admin_menu() {
        add_menu_page(
            'GG Instagram',
            'Instagram',
            'manage_options',
            'gg-instagram',
            [$this, 'settings_page'],
            'dashicons-instagram'
        );

        add_submenu_page(
            'gg-instagram',
            'Redirect',
            'Redirect Handler',
            'manage_options',
            'gg-instagram-redirect',
            [$this, 'redirect_handler']
        );

        add_submenu_page(
            'gg-instagram',
            'Deauthorize',
            'Deauthorize Handler',
            'manage_options',
            'gg-instagram-deauthorize',
            [$this, 'deauthorize_handler']
        );

        add_submenu_page(
            'gg-instagram',
            'Deletion',
            'Deletion Handler',
            'manage_options',
            'gg-instagram-deletion',
            [$this, 'deletion_handler']
        );
    }

    public function deletion_handler() {
		// Delete the Instagram profile
		global $wpdb;
		$site_id = get_current_blog_id();
		$table_name = $wpdb->prefix . 'gg_instagram';
		$wpdb->delete($table_name, ['site_id' => $site_id]);

		if ($wpdb->last_error) {
			wp_send_json_error(['message' => 'Database error: ' . $wpdb->last_error]);
		} else {
			wp_send_json_success(['message' => 'Instagram profile deleted successfully']);
		}

	}

    public function deauthorize_handler() {
		// Deauthorize the Instagram profile
		global $wpdb;
		$site_id = get_current_blog_id();
		$table_name = $wpdb->prefix . 'gg_instagram';
		$wpdb->delete($table_name, ['site_id' => $site_id]);

		if ($wpdb->last_error) {
			wp_send_json_error(['message' => 'Database error: ' . $wpdb->last_error]);
		} else {
			wp_send_json_success(['message' => 'Instagram profile deauthorized successfully']);
		}
	}

    public function settings_page() {
        echo '<div class="wrap">';
        echo '<h1>Connect Your Instagram Profile</h1>';
        echo '<button id="connect-instagram">Connect to Instagram</button>';
        echo '<p id="status-message"></p>';
        echo '</div>';
    }

	public function redirect_handler() {
		// Check if the authorization code is present
		if (isset($_GET['code'])) {
			$auth_code = sanitize_text_field($_GET['code']);

			// Proceed to exchange the code for an access token once the page parameter is set
			$access_token = $this->exchange_code_for_token($auth_code);
echo $access_token;
			if ($access_token) {
				// Send the access token back to the opener window
				?>
				<script>
					window.opener.postMessage({
						accessToken: '<?php echo esc_js($access_token); ?>',
						success: true
					}, "*");
					window.close();
				</script>
				<?php
			} else {
				// Handle failure to retrieve access token
				?>
				<script>
					window.opener.postMessage({
						error: 'Failed to retrieve access token',
						success: false
					}, "*");
					// window.close();
				</script>
				<?php
			}
		} else {
			// Handle case where no authorization code is found
			?>
			<script>
				window.opener.postMessage({
					error: 'No authorization code found',
					success: false
				}, "*");
				window.close();
			</script>
			<?php
		}
	}

	private function exchange_code_for_token($code) {
		$client_id = '1992699081248165';
		$client_secret = '0070620a3b69b6b7eca1c48e34f8296a';
		$redirect_uri = 'https://grey-penny.localsite.io/wp-admin/admin.php?page=gg-instagram-redirect';
		$body_req = [
			'client_id' => $client_id,
			'client_secret' => $client_secret,
			'grant_type' => 'authorization_code',
			'redirect_uri' => $redirect_uri, // Must match exactly
			'code' => sanitize_text_field($code)
		];


/*
curl -X POST \
  https://api.instagram.com/oauth/access_token \
  -F client_id=1992699081248165 \
  -F client_secret=0070620a3b69b6b7eca1c48e34f8296a \
  -F grant_type=authorization_code \
  -F redirect_uri=https://grey-penny.localsite.io/wp-admin/admin.php?page=gg-instagram-redirect \
  -F code=AQCM4DXP_M9quQF-ceYUJNG7P7IpdXPp_CQ58lZGRVP2NksTW8kTQomT36C2veO3rKi9PsTOLq5eWgXCJcQJ8q3LZDvugo-npHC4foli5ix0EAUyNw-CD3cTXRwFK3y85YzfG_ipZaNhNzDpCIl5nmSbT5eMzCSxYFHko6ees8Kndz5ZgLbiOFf-57ybHPMyNRtV7PIeQVoDiEMUA0QD-cEVdtPgWjj7ABVKRZ0NIzRPOQ

*/

		$response = wp_remote_post('https://api.instagram.com/oauth/access_token', [
			'body' => $body_req,
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded'
			]
		]);

		if (is_wp_error($response)) {
			return false; // Handle error accordingly
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);
		echo '<pre>';
		print_r( $body_req);
		print_r( $body);
		echo '</pre>';
		return $body['access_token'] ?? false; // Return the access token
	}



    public function exchange_code_for_tokenXX($code) {
        // Exchange the authorization code for an access token
        $response = wp_remote_post('https://api.instagram.com/oauth/access_token', [
            'body' => [
                'client_id'     => '1992699081248165',
                'client_secret' => '0070620a3b69b6b7eca1c48e34f8296a',
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => admin_url('admin.php?page=gg-instagram-redirect'),
                'code'          => $code
            ]
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
		wp_send_json($body);
        $data = json_decode($body, true);

        return $data['access_token'] ?? false;
    }

    public function gg_save_instagram_data() {
        // Check if the user has the necessary permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }

        // Check for the access token in the request
        if (!isset($_POST['access_token'])) {
            wp_send_json_error(['message' => 'No access token provided']);
        }

        // Sanitize the access token
        $access_token = sanitize_text_field($_POST['access_token']);
        $site_id = get_current_blog_id();
        global $wpdb;

        // Save the access token and related information in the gg_instagram table
        $table_name = $wpdb->prefix . 'gg_instagram';
        $wpdb->insert($table_name, [
            'site_id' => $site_id,
            'access_token' => $access_token,
            'profile_name' => 'Example Profile Name' // You can modify this to use actual data
        ]);

        if ($wpdb->last_error) {
            wp_send_json_error(['message' => 'Database error: ' . $wpdb->last_error]);
        } else {
            wp_send_json_success(['message' => 'Instagram profile saved successfully']);
        }
    }

    public function enqueue_scripts() {
        // Enqueue admin scripts and localize the AJAX URL
        wp_enqueue_script('gg-instagram-admin', plugin_dir_url(__FILE__) . '../src/admin.js', ['jquery'], null, true);
        wp_localize_script('gg-instagram-admin', 'ggInstagram', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'admin_url' => admin_url('admin.php'),
        ]);
    }

    public function handle_redirect() {
        // This method is called on every page load, but it's handled in redirect_handler
        // No implementation needed here if handled directly in redirect_handler
    }
}
