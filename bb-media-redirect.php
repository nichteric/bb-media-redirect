<?php

/*
Plugin Name: BB Media Redirect
Description: Simply redirect all media files to another WP instance (e.g. for staging instance)
Version: 1.2
Author: Eric Leclercq <eric@curious.care>
Requires at least: 6.0
Requires PHP: 7.0
BB Update Checker: enabled
*/

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
	exit('We\'re sorry, but you can not directly access this file.');
}

class BBMediaRedirect
{
	public function __construct()
	{
		if (is_admin()) {
			// Add menus to Admin UI
			add_action('admin_menu', function () {
				add_options_page('Media Redirect', 'Media Redirect', 'install_plugins', 'bb_media_redirect', [$this, 'redirect_page']);
			});

			// Include style and script
			add_action('admin_enqueue_scripts', function () {
				wp_enqueue_style('bb_media_redirect', plugin_dir_url(__FILE__) . 'style.css');
				wp_enqueue_script('bb_media_redirect', plugin_dir_url(__FILE__) . 'script.js', [], '', false);
			});

			// Handle POST requests
			add_action('admin_init', function () {
				$this->handle_post();
			});

			// Status item in menu bar
			add_action(
				'admin_bar_menu',
				function ($admin_bar) {
					$args = [
						'id' => 'bb_media_redirect_status',
						'title' => 'BB Media Redirect',
						'href' => get_admin_url() . 'options-general.php?page=bb_media_redirect',
						'meta' => ['title' => __('BB Media Redirect')]
					];
					$admin_bar->add_menu($args);
				},
				900
			);
		} else {
			// Only activate redirection hook if option is set
			if (get_option('bb_media_redirect')) {
				add_action('template_redirect', [$this, 'media_redirect'], 0);
			}
		}
	}

	public function redirect_page()
	{
		include 'page-redirect.php';
	}

	public function media_redirect_test()
	{
		// Get a random image from the media library
        // Fixme: sometimes getting a missing image???
		$args = ['post_type' => 'attachment', 'post_mime_type' => 'image', 'post_status' => 'inherit', 'orderby' => 'rand', 'posts_per_page' => '1'];
		$query = new WP_Query($args);

		if ($query->have_posts()) {
			$query->the_post();
			$test_image = wp_get_attachment_image_url(get_the_ID());
			echo "<img id=\"bb_test_img\" onload=\"bb_redir_ok()\" onerror=\"bb_redir_fail()\" src=\"{$test_image}\">";
		} else {
			echo '<script>bb_redir_fail();</script>';
			return false;
		}
		wp_reset_postdata();
	}

	// Hook to redirect media
	public function media_redirect()
	{
		$dst = get_option('bb_media_redirect_to') . '/wp-content/uploads';
		$uri = $_SERVER['REQUEST_URI'];
		if (str_contains($uri, '/wp-content/uploads/')) {
			$uri = preg_replace('/.*\\/wp-content\\/uploads\\//', '', $uri);
			header("Location: {$dst}/{$uri}");
			exit();
		}
	}

	public function admin_notice($type, $message)
	{
		if (!in_array($type, ['error', 'warning', 'success', 'info'])) {
			return;
		}
		$message = htmlspecialchars($message);
		add_action('admin_notices', function () use ($type, $message) {
			echo "<div class=\"notice notice-{$type} is-dismissible\"><p><strong>{$message}</strong></p></div>";
		});
	}

	public function handle_post()
	{
		if (isset($_POST['bb_redirect_settings_nonce']) && wp_verify_nonce($_POST['bb_redirect_settings_nonce'], 'bb_redirect_settings_save')) {
			$redirect_to = $_POST['bb_media_redirect_to'];
			if ($redirect_to == '') {
				update_option('bb_media_redirect', false);
				update_option('bb_media_redirect_to', '');
				$this->admin_notice('success', 'Settings saved!');
			} elseif (wp_http_validate_url($redirect_to)) {
				update_option('bb_media_redirect_to', rtrim($redirect_to, '/'));
				update_option('bb_media_redirect', isset($_POST['bb_media_redirect']));
				$this->admin_notice('success', 'Settings saved!');
			} else {
				update_option('bb_media_redirect_to', '');
				update_option('bb_media_redirect', false);
				$this->admin_notice('error', 'Invalid value "Redirect To".');
			}
		}
	}
}

new BBMediaRedirect();
