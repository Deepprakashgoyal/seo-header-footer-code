<?php 
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}


 ?>

<section class="shfc-main">
	<h2>SEO Global Header Footer Code:</h2>
	<form action="options.php" method="POST">
		<?php settings_fields('shfc_plugin_options'); ?>
		<table class="form-table">
			<tr>
				<th>Header Code <p>Insert your global header code here:</p></th>
				<td>
					<textarea name="shfc_global_code[global_header_code]" id="" rows="5" 	placeholder="Enter Your code here:"><?php if (isset($shfc_option_values['global_header_code'])) echo esc_textarea($shfc_option_values['global_header_code']); ?></textarea>
				</td>
			</tr>

			<tr>
				<th>Body Code <p>Insert your global body code here:</p></th>
				<td><textarea name="shfc_global_code[global_body_code]" id="" rows="5" 	placeholder="Enter Your code here:"><?php if (isset($shfc_option_values['global_body_code'])) echo esc_textarea($shfc_option_values['global_body_code']); ?></textarea>
				</td>
			</tr>

			<tr>
				<th>Footer Code<p>Insert your global footer code here:</p></th>
				<td><textarea name="shfc_global_code[global_footer_code]" id="" rows="5" 	placeholder="Enter Your code here:"><?php if (isset($shfc_option_values['global_footer_code'])) echo esc_textarea($shfc_option_values['global_footer_code']); ?></textarea>
				</td>
			</tr>
		</table>

		<input type="submit" name="submit" value="Submit Code" class="button-primary">
	</form>
</section>


<style>
	.shfc-main{
		padding: 20px;
/*		background: #fff;*/
		margin-top: 20px;
	}
	.shfc-main label{
		display: block;
	}

	.form-table textarea{
		width: 100%;
	}
	h3{
		margin-bottom: 40px;
	}
</style>
