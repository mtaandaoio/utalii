<?php
if(!defined('ABSPATH')) {exit;}
if( !class_exists( 'UTALII_Settings') ){
	class UTALII_Settings {
		
		private $tax = 9;
		private $advanced_booking_limit = "-1";
		private $is_tax_apply = "yes";
		private $is_poa_enable = "yes";
		private $is_paypal_enable = "yes";
		private $paypal_mode = "live";
		private $paypal_email = "";
		
		private static $_instance = null;
		
		
		public static function get_instance(){
			if( is_null(self::$_instance) ){
				self::$_instance = new self();
			}
			
			return self::$_instance;
		}
		
		function __construct(){
			add_action( 'admin_menu', array( $this, 'register_menu' ) );
			$this->set_settings();
			$this->get_settings();
		}
		
		function register_menu(){
			$parent_slug	=	'utalii';
			$page_title		=	'Settings';
			$menu_title		=	'Settings';
			$capability		=	'manage_options';
			$menu_slug		=	'utalii-settings';
			$function		=	'add_menu';
			
			add_submenu_page(
				$parent_slug,
				$page_title,
				$menu_title,
				$capability,
				$menu_slug,
				array( $this, $function )
			);
		}
		
		function add_menu(){
		?>
		<div class="wrap">
			<h2>UTALII Settings</h2>
			<form name="utalii_settings_form" id="utalii_settings_form" class="" action="" method="POST">
			<table class="form-table">
				<tr>
					<th>
						<label for="utalii_settings_advanced_booking_limit">Advanced booking limit:</label>
					</th>
					<td>
						<input type="number" min="-1" value="<?php echo $this->advanced_booking_limit; ?>" name="advanced_booking_limit" id="utalii_settings_advanced_booking_limit" class="" /> Month(s)
						<br>
						(for how long a customer can book in advance, -1 for unlimited, 0 for only current month)
					</td>
				</tr>
				<tr>
					<th>
						<label for="utalii_settings_is_tax_apply">Enable Tax:</label>
					</th>
					<td>
						<input type="checkbox" name="is_tax_apply" id="utalii_settings_is_tax_apply" value="yes" <?php checked( $this->is_tax_apply, 'yes' ); ?> />
					</td>
				</tr>
				<tr>
					<th>
						<label for="utalii_settings_tax">Tax(%):</label>
					</th>
					<td>
						<?php
							$disabled = '';
							if( "no" == $this->is_tax_apply ){
								$disabled = 'disabled="disabled"';
							}
						?>
						<input type="number" step="any" name="tax" class="" id="utalii_settings_tax" value="<?php echo $this->tax; ?>" <?php echo $disabled; ?> />%
					</td>
				</tr>
				<tr>
					<th>
						Pyament Mode:
					</th>
					<td>
						<input type="checkbox" name="is_poa_enable" id="utalii_settings_is_poa_enable" value="yes" <?php checked( $this->is_poa_enable, 'yes' ); ?> />
						<label for="utalii_settings_is_poa_enable">Enable payment on arrival</label>
						
						<br />
						
						<input type="checkbox" name="is_paypal_enable" id="utalii_settings_is_paypal_enable" value="yes" <?php checked( $this->is_paypal_enable, 'yes' ); ?> />
						<label for="utalii_settings_is_paypal_enable">Enable Pyapal</label>
					</td>
				</tr>
				<tr>
					<th>
						Paypal mode:
					</th>
					<td>
						<?php
							$disabled = '';
							if( "no" == $this->is_paypal_enable ){
								$disabled = 'disabled="disabled"';
							}
						?>
						<input type="radio" name="paypal_mode" id="utalii_settings_paypal_mode_live" value="live" <?php checked( $this->paypal_mode, 'live' ); ?> <?php echo $disabled; ?> />
						<label for="utalii_settings_paypal_mode_live">Live</label>
						
						<input type="radio" name="paypal_mode" id="utalii_settings_paypal_mode_sandbox" value="sandbox" <?php checked( $this->paypal_mode, 'sandbox' ); ?> <?php echo $disabled; ?> />
						<label for="utalii_settings_paypal_mode_sandbox">Sandbox</label>
					</td>
				</tr>
				<tr>
					<th>
						<label for="utalii_settings_paypal_email">Paypal email:</label>
					</th>
					<td>
						<?php
							$disabled = '';
							if( "no" == $this->is_paypal_enable ){
								$disabled = 'disabled="disabled"';
							}
						?>
						<input type="text" name="paypal_email" class="" id="utalii_settings_paypal_email" value="<?php echo $this->paypal_email; ?>" <?php echo $disabled; ?> />
					</td>
				</tr>
			</table>
			<input type="submit" name="utalii_submit_settings" id="" value="Save Settings" class="button button-primary" />
			</form>
		</div>
		<script type="application/javascript">
			jQuery(document).ready(function($){
				$("#utalii_settings_is_tax_apply").click(function(){
					if( $(this).is(":checked") ){
						$("#utalii_settings_tax").prop("disabled", false );
					} else {
						$("#utalii_settings_tax").prop("disabled", true );
					}
				});
				
				$("#utalii_settings_is_paypal_enable").click(function(){
					if( $(this).is(":checked") ){
						$("#utalii_settings_paypal_mode_live").prop("disabled", false );
						$("#utalii_settings_paypal_mode_sandbox").prop("disabled", false );
						$("#utalii_settings_paypal_email").prop("disabled", false );
					} else {
						$("#utalii_settings_paypal_mode_live").prop("disabled", true );
						$("#utalii_settings_paypal_mode_sandbox").prop("disabled", true );
						$("#utalii_settings_paypal_email").prop("disabled", true );
					}
				});
			});
		</script>
		<?php
		}
		
		function get_settings(){
			/* tax */
			$this->is_tax_apply = get_option( 'utalii_tax_apply', "no" );
			
			$tax = get_option( 'utalii_tax', '9' );
			if( is_numeric( $tax ) ){
				$this->tax = $tax;
			}
			
			/* payment modes */
			$this->advanced_booking_limit = get_option( 'utalii_advanced_booking_limit', "-1" );
			$this->is_poa_enable = get_option( 'utalii_poa_enable', "no" );
			$this->is_paypal_enable = get_option( 'utalii_pyapal_enable', "no" );
			$this->paypal_mode = get_option( 'utalii_pyapal_mode', "sandbox" );
			$this->paypal_email = get_option( 'utalii_paypal_email', "" );
		}
		
		function set_settings(){
			if( isset( $_POST['utalii_submit_settings'] ) ){
				/*advanced_booking_limit*/
				if( isset( $_POST['advanced_booking_limit'] ) ){
					$advanced_booking_limit = trim( $_POST['advanced_booking_limit'] );
					if( is_numeric( $advanced_booking_limit ) ){
						update_option( 'utalii_advanced_booking_limit', $advanced_booking_limit );
					}
				}
				
				/* tax */
				if( isset( $_POST['is_tax_apply'] ) ){
					$is_tax_apply = $_POST['is_tax_apply'];
					if( "yes" == $is_tax_apply ){
						update_option('utalii_tax_apply', "yes");
					} else {
						update_option('utalii_tax_apply', "no");
					}
				} else {
					update_option('utalii_tax_apply', "no");
				}
				
				if( isset( $_POST['tax'] ) ){
					$tax = trim( $_POST['tax'] );
					update_option( 'utalii_tax', $tax );
				}
				
				/* payment modes */
				if( isset( $_POST['is_poa_enable'] ) ){
					$is_poa_enable = $_POST['is_poa_enable'];
					if( "yes" == $is_poa_enable ){
						update_option('utalii_poa_enable', "yes");
					} else {
						update_option('utalii_poa_enable', "no");
					}
				} else {
					update_option('utalii_poa_enable', "no");
				}
				
				if( isset( $_POST['is_paypal_enable'] ) ){
					$is_paypal_enable = $_POST['is_paypal_enable'];
					if( "yes" == $is_paypal_enable ){
						update_option('utalii_pyapal_enable', "yes");
					} else {
						update_option('utalii_pyapal_enable', "no");
					}
				} else {
					update_option('utalii_pyapal_enable', "no");
				}
				
				if( isset( $_POST['paypal_mode'] ) ){
					$paypal_mode = $_POST['paypal_mode'];
					if( "live" == $paypal_mode ){
						update_option('utalii_pyapal_mode', "live");
					} else {
						update_option('utalii_pyapal_mode', "sandbox");
					}
				} else {
					update_option('utalii_pyapal_mode', "sandbox");
				}
				
				if( isset( $_POST['paypal_email'] ) ){
					$paypal_email = trim( $_POST['paypal_email'] );
					if( is_email( $paypal_email ) ){
						update_option('utalii_paypal_email', $paypal_email );
					}
				}
				
			}
		}
		
	} /* end of class */
}

UTALII_Settings::get_instance();
