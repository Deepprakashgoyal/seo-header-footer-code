<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="wrap">
	<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
	<form method="post" action="options.php">
		<?php
		settings_fields('shfc_plugin_options');
		$shfc_option_values = get_option('shfc_global_code', $this->get_default_options());
		?>

		<h2>Allowed Post Types</h2>
		<p>Select which post types should have the SEO Header Footer Master fields:</p>
		<table class="form-table">
			<tr>
				<th scope="row">Post Types</th>
				<td>
					<?php
					$post_types = get_post_types(array('public' => true), 'objects');
					$allowed_post_types = isset($shfc_option_values['allowed_post_types']) ? $shfc_option_values['allowed_post_types'] : array('post', 'page');

					foreach ($post_types as $post_type) {
						if ($post_type->name === 'attachment') continue; // Skip media attachments
					?>
						<label style="display: block; margin-bottom: 8px;">
							<input type="checkbox"
								name="shfc_global_code[allowed_post_types][]"
								value="<?php echo esc_attr($post_type->name); ?>"
								<?php checked(in_array($post_type->name, $allowed_post_types)); ?>>
							<?php echo esc_html($post_type->label); ?>
						</label>
					<?php
					}
					?>
				</td>
			</tr>
		</table>

		<h2>Global Code</h2>
		<p>Add code that will be applied globally across your site:</p>

		<table class="form-table">
			<tr>
				<th scope="row">Header Code</th>
				<td>
					<textarea name="shfc_global_code[global_header_code]" rows="5" cols="50" class="large-text code"><?php echo esc_textarea($shfc_option_values['global_header_code']); ?></textarea>
					<p class="description">Add code that will be inserted in the &lt;head&gt; section of your site.</p>
				</td>
			</tr>
			<tr>
				<th scope="row">Body Code</th>
				<td>
					<textarea name="shfc_global_code[global_body_code]" rows="5" cols="50" class="large-text code"><?php echo esc_textarea($shfc_option_values['global_body_code']); ?></textarea>
					<p class="description">Add code that will be inserted right after the opening &lt;body&gt; tag.</p>
				</td>
			</tr>
			<tr>
				<th scope="row">Footer Code</th>
				<td>
					<textarea name="shfc_global_code[global_footer_code]" rows="5" cols="50" class="large-text code"><?php echo esc_textarea($shfc_option_values['global_footer_code']); ?></textarea>
					<p class="description">Add code that will be inserted before the closing &lt;/body&gt; tag.</p>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>
</div>

<style>
	.shfc-main {
		padding: 20px;
		/*		background: #fff;*/
		margin-top: 20px;
	}

	.shfc-main label {
		display: block;
	}

	.form-table textarea {
		width: 100%;
	}

	h3 {
		margin-bottom: 40px;
	}
</style>