<div class="youpay-style">
	<div class="yp-login-box">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="process_youpay_login">

			<img src="<?php echo $this->youpay->resource_root; ?>/images/youpay-logo.svg" width="200">

			<div class="yp-form-control">
				<label for="email">Email address</label>
				<input id="email" name="email"
				   type="text"
				   placeholder=""/>
			</div>

			<div class="yp-form-control">
				<label id="password">Password</label>
				<input id="password" name="password" type="password" placeholder="" />
			</div>

			<?php if ( ! empty( $_GET['yperror'] ) ) : ?>
				<p class="yp-error">Invalid Credentials, please try again.</p>
			<?php endif; ?>

            <?php echo wp_nonce_field( 'youpay-login' ); ?>

			<input type="submit" value="Login">
		</form>
	</div>
</div>
