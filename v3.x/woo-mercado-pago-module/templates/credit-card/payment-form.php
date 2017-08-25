<?php

/**
 * Part of Woo Mercado Pago Module
 * Author - Mercado Pago
 * Developer - Marcelo Tomio Hama / marcelo.hama@mercadolivre.com
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div width="100%" style="margin:1px; padding:36px 36px 16px 36px; background:white;">
	<img class="logo" src="<?php echo ($images_path . 'mplogo.png'); ?>" width="156" height="40"/>
	<?php if ( ! empty( $banner_path ) ) : ?>
		<img class="mp-creditcard-banner" src="<?php echo $banner_path;?>" width="312" height="40"/>
	<?php endif; ?>
</div>
<fieldset style="background:white;">

	<!-- coupom -->
	<div class="mp-box-inputs mp-line" id="mercadopago-form-coupon" style="margin-bottom: 16px;" >
		<div class="form-row" >
			<div class="form-col-8">
				<label for="couponCodeLabel"><?php echo __( 'Discount Coupon', 'woo-mercado-pago-module' ); ?></label>
				<input class="form-control-mine" type="text" id="couponCode" name="mercadopago_custom[coupon_code]"
					autocomplete="off" maxlength="24" style="margin-bottom: 8px;"/>
			</div>
			<div class="form-col-4">
				<label >&nbsp;</label>
				<input class="form-control-mine" type="button" class="button" id="applyCoupon" value="<?php echo __( 'Apply', 'woo-mercado-pago-module' ); ?>">
			</div>
			<span class="mp-discount" id="mpCouponApplyed" style="width:98%; margin:6px;"></span>
			<span class="mp-error" id="mpCouponError" style="width:98%; margin:6px;"></span>
		<div class="form-row" >
	</div>

	<!-- payment method -->
	<div id="mercadopago-form-customer-and-card" class="mp-box-inputs mp-line">
		<div id="form-ticket">
			<div class="form-row" >
				<div class="form-col-8" >
					<label for="paymentMethodIdSelector">
						<?php echo __( 'Payment Method', 'woo-mercado-pago-module' ); ?><em class="obrigatorio"> *</em>
					</label>
					<select id="paymentMethodSelector" class="form-control-mine" name="mercadopago_custom[paymentMethodSelector]"
						data-checkout="cardId" style="background-origin: 320px; width: 100%;" >
						<optgroup label=<?php echo __( 'Your Card', 'woo-mercado-pago-module' ); ?> id="payment-methods-for-customer-and-cards">
						<?php foreach ( $customer_cards as $card ) : ?>
							<option value=<?php echo $card['id']; ?>
							first_six_digits=<?php echo $card['first_six_digits']; ?>
							last_four_digits=<?php echo $card['last_four_digits']; ?>
							security_code_length=<?php echo $card['security_code']['length']; ?>
							type_checkout='customer_and_card'
							payment_method_id=<?php echo $card['payment_method']['id']; ?>>
								<?php echo ucfirst( $card['payment_method']['name'] ); ?>
								<?php echo __( 'ended in', 'woo-mercado-pago-module' ); ?>
								<?php echo $card['last_four_digits']; ?>
							</option>
						<?php endforeach; ?>
						</optgroup>
						<optgroup label="<?php echo __( 'Other Cards', 'woo-mercado-pago-module' ); ?>" id="payment-methods-list-other-cards">
							<option value="-1"><?php echo __( 'Other Card', 'woo-mercado-pago-module' ); ?></option>
						</optgroup>
					</select>
				</div>
				<div class="form-col-4" id="mp-securityCode-customer-and-card">
					<label for="customer-and-card-securityCode">
						<?php echo __( 'Security code', 'woo-mercado-pago-module' ); ?><em class="obrigatorio"> *</em>
					</label>
					<input type="text" id="customer-and-card-securityCode" data-checkout="securityCode"
						autocomplete="off" class="form-control-mine" maxlength="4"
						style="width: 100%; padding: 8px; background: url(<?php echo ($images_path . 'cvv.png'); ?>) 98% 50% no-repeat;"/>
					<span class="mp-error" id="mp-error-224" data-main="#customer-and-card-securityCode">
						<?php echo __( 'Parameter securityCode can not be null/empty', 'woo-mercado-pago-module' ); ?>
					</span>
					<span class="mp-error" id="mp-error-E302" data-main="#customer-and-card-securityCode">
						<?php echo __( 'Invalid Security Code', 'woo-mercado-pago-module' ); ?>
					</span>
					<span class="mp-error" id="mp-error-E203" data-main="#customer-and-card-securityCode">
						<?php echo __( 'Invalid Security Code', 'woo-mercado-pago-module' ); ?>
					</span>
				</div>
			</div>
		</div>
	</div>

	<div id="mercadopago-form" class="mp-box-inputs mp-line">
		<div id="form-ticket">
			<!-- Card Number -->
			<div class="form-row" >
				<div class="form-col-12">
					<label for="cardNumber"><?php echo __( 'Credit card number', 'woo-mercado-pago-module' ); ?><em class="obrigatorio"> *</em></label>
					<input type="text" id="cardNumber" data-checkout="cardNumber"
						class="form-control-mine" autocomplete="off" maxlength="19" style="width: 100%;"/>
					<span class="mp-error" id="mp-error-205" data-main="#cardNumber"><?php echo __( 'Parameter cardNumber can not be null/empty', 'woo-mercado-pago-module' ); ?></span>
					<span class="mp-error" id="mp-error-E301" data-main="#cardNumber"><?php echo __( 'Invalid Card Number', 'woo-mercado-pago-module' ); ?></span>
				</div>
			</div>
			<!-- Expiry Date -->
			<div class="form-row" >
				<div class="form-col-6">
					<label for="cardExpirationMonth"><?php echo __( 'Expiration month', 'woo-mercado-pago-module' ); ?><em class="obrigatorio"> *</em></label>
					<select id="cardExpirationMonth" data-checkout="cardExpirationMonth" class="form-control-mine"
						name="mercadopago_custom[cardExpirationMonth]" style="width: 100%;">
						<option value="-1"> <?php echo __( 'Month', 'woo-mercado-pago-module' ); ?> </option>
						<?php for ($x=1; $x<=12; $x++) : ?>
							<option value="<?php echo $x; ?>"> <?php echo $x; ?></option>
						<?php endfor; ?>
					</select>
					<span class="mp-error" id="mp-error-208" data-main="#cardExpirationMonth"><?php echo __( 'Invalid Expiration Date', 'woo-mercado-pago-module' ); ?></span>
					<span class="mp-error" id="mp-error-325" data-main="#cardExpirationMonth"><?php echo __( 'Invalid Expiration Date', 'woo-mercado-pago-module' ); ?></span>
				</div>
				<div class="form-col-6">
					<label for="cardExpirationYear"><?php echo __( 'Expiration year', 'woo-mercado-pago-module' ); ?><em class="obrigatorio"> *</em></label>
					<select id="cardExpirationYear" data-checkout="cardExpirationYear" class="form-control-mine"
						name="mercadopago_custom[cardExpirationYear]" style="width: 100%;">
						<option value="-1"> <?php echo __( 'Year', 'woo-mercado-pago-module' ); ?> </option>
						<?php for ($x=date("Y"); $x<= date("Y") + 10; $x++) : ?>
							<option value="<?php echo $x; ?>"> <?php echo $x; ?> </option>
						<?php endfor; ?>
					</select>
					<span class="mp-error" id="mp-error-209" data-main="#cardExpirationYear"> </span>
					<span class="mp-error" id="mp-error-326" data-main="#cardExpirationYear"> </span>
				</div>
			</div>
			<!-- Card Holder Name -->
			<div class="form-row" >
				<div class="form-col-12">
					<label for="cardholderName"><?php echo __( 'Card holder name', 'woo-mercado-pago-module' ); ?><em class="obrigatorio"> *</em></label>
					<input type="text" id="cardholderName" name="mercadopago_custom[cardholderName]" class="form-control-mine"
						data-checkout="cardholderName" autocomplete="off" style="width: 100%;"/>
					<span class="mp-error" id="mp-error-221" data-main="#cardholderName"><?php echo __( 'Parameter cardholderName can not be null/empty', 'woo-mercado-pago-module' ); ?></span>
					<span class="mp-error" id="mp-error-316" data-main="#cardholderName"><?php echo __( 'Invalid Card Holder Name', 'woo-mercado-pago-module' ); ?></span>
				</div>
			</div>
			<!-- CVV and Issuer -->
			<div class="form-row" >
				<div class="form-col-6">
					<label for="securityCode"><?php echo __( 'Security code', 'woo-mercado-pago-module' ); ?><em class="obrigatorio"> *</em></label>
					<input type="text" id="securityCode" data-checkout="securityCode" autocomplete="off" class="form-control-mine" maxlength="4"
						style="width: 100%; padding: 8px; background: url(<?php echo ($images_path . 'cvv.png'); ?>) 98% 50% no-repeat;" />
					<span class="mp-error" id="mp-error-224" data-main="#securityCode"><?php echo __( 'Parameter securityCode can not be null/empty', 'woo-mercado-pago-module' ); ?></span>
					<span class="mp-error" id="mp-error-E302" data-main="#securityCode"><?php echo __( 'Invalid Security Code', 'woo-mercado-pago-module' ); ?></span>
				</div>
				<div class="form-col-6 mp-issuer">
					<label for="issuer"><?php echo __( 'Issuer', 'woo-mercado-pago-module' ); ?><em class="obrigatorio"> *</em></label>
					<select id="issuer" data-checkout="issuer" class="form-control-mine"
						name="mercadopago_custom[issuer]" style="width: 100%;"></select>
					<span class="mp-error" id="mp-error-220" data-main="#issuer"><?php echo __( 'Parameter cardIssuerId can not be null/empty', 'woo-mercado-pago-module' ); ?></span>
				</div>
			</div>
			<!-- Document Type -->
			<div class="form-row" >
				<div class="form-col-6 mp-docType">
					<label for="docType"><?php echo __( 'Document type', 'woo-mercado-pago-module' ); ?><em class="obrigatorio"> *</em></label>
					<select id="docType" data-checkout="docType" name="mercadopago_custom[docType]"
						class="form-control-mine" style="width: 100%;"></select>
					<span class="mp-error" id="mp-error-212" data-main="#docType"><?php echo __( 'Parameter docType can not be null/empty', 'woo-mercado-pago-module' ); ?></span>
					<span class="mp-error" id="mp-error-322" data-main="#docType"><?php echo __( 'Invalid Document Type', 'woo-mercado-pago-module' ); ?></span>
				</div>
				<div class="form-col-6 mp-docNumber">
					<label for="docNumber"><?php echo __( 'Document number', 'woo-mercado-pago-module' ); ?><em class="obrigatorio"> *</em></label>
					<input type="text" id="docNumber" data-checkout="docNumber" class="form-control-mine"
						name="mercadopago_custom[docNumber]" autocomplete="off" style="width: 100%;"/>
					<span class="mp-error" id="mp-error-214" data-main="#docNumber"><?php echo __( 'Parameter docNumber can not be null/empty', 'woo-mercado-pago-module' ); ?></span>
					<span class="mp-error" id="mp-error-324" data-main="#docNumber"><?php echo __( 'Invalid Document Number', 'woo-mercado-pago-module' ); ?></span>
				</div>
			</div>
		</div>
	</div>

	<div id="mp-box-installments" class="mp-box-inputs mp-line">
		<div id="form-ticket">
			<div class="form-row" >
				<div id="mp-box-installments-selector" class="form-col-8">
					<label for="installments">
						<?php echo __( 'Installments', 'woo-mercado-pago-module' ); ?>
						<?php if ( $currency_ratio != 1 ) :
							echo " (" . __( 'Payment converted from', 'woo-mercado-pago-module' ) . " " .
							$woocommerce_currency . " " . __( 'to', 'woo-mercado-pago-module' ) . " " .
							$account_currency . ")";
						endif; ?> <em>*</em>
					</label>
					<select id="installments" data-checkout="installments" class="form-control-mine"
						name="mercadopago_custom[installments]" style="width: 100%;"></select>
				</div>
				<div id="mp-box-input-tax-cft" class="form-col-4">
					<div id="mp-box-input-tax-tea"><div id="mp-tax-tea-text"></div></div>
					<div id="mp-tax-cft-text"></div>
				</div>
			</div>
		</div>
	</div>

	<div class="mp-box-inputs mp-line" >
		<!-- NOT DELETE LOADING-->
		<div class="mp-box-inputs mp-col-25">
			<div id="mp-box-loading"></div>
		</div>
	</div>

	<div class="mp-box-inputs mp-col-100" id="mercadopago-utilities" >
		<input type="hidden" id="site_id" name="mercadopago_custom[site_id]"/>
		<input type="hidden" id="amount" value='<?php echo $amount; ?>' name="mercadopago_custom[amount]"/>
		<input type="hidden" id="campaign_id" name="mercadopago_custom[campaign_id]"/>
		<input type="hidden" id="campaign" name="mercadopago_custom[campaign]"/>
		<input type="hidden" id="discount" name="mercadopago_custom[discount]"/>
		<input type="hidden" id="paymentMethodId" name="mercadopago_custom[paymentMethodId]"/>
		<input type="hidden" id="token" name="mercadopago_custom[token]"/>
		<input type="hidden" id="cardTruncated" name="mercadopago_custom[cardTruncated]"/>
		<input type="hidden" id="CustomerAndCard" name="mercadopago_custom[CustomerAndCard]"/>
		<input type="hidden" id="CustomerId" value='<?php echo $customerId; ?>' name="mercadopago_custom[CustomerId]"/>
	</div>

</fieldset>

<script type="text/javascript">

	// === Instantiation

	var mercadopago_site_id = "<?php echo $site_id; ?>";
	var mercadopago_public_key = "<?php echo $public_key; ?>";
	var mercadopago_payer_email = "<?php echo $payer_email; ?>";
	var mercadopago_coupon_mode = "<?php echo $coupon_mode; ?>";
	var mercadopago_discount_action_url = "<?php echo $discount_action_url; ?>";

	MPv1.text.choose = "<?php echo $form_labels['form']['label_choose']; ?>";
	MPv1.text.other_bank = "<?php echo $form_labels['form']['label_other_bank']; ?>";
	MPv1.text.discount_info1 = "<?php echo $form_labels['form']['discount_info1']; ?>";
	MPv1.text.discount_info2 = "<?php echo $form_labels['form']['discount_info2']; ?>";
	MPv1.text.discount_info3 = "<?php echo $form_labels['form']['discount_info3']; ?>";
	MPv1.text.discount_info4 = "<?php echo $form_labels['form']['discount_info4']; ?>";
	MPv1.text.discount_info5 = "<?php echo $form_labels['form']['discount_info5']; ?>";
	MPv1.text.discount_info6 = "<?php echo $form_labels['form']['discount_info6']; ?>";
	MPv1.text.apply = "<?php echo $form_labels['form']['apply']; ?>";
	MPv1.text.remove = "<?php echo $form_labels['form']['remove']; ?>";
	MPv1.text.coupon_empty = "<?php echo $form_labels['form']['coupon_empty']; ?>";
	MPv1.paths.loading = "<?php echo ( $images_path . 'loading.gif' ); ?>";
	MPv1.paths.check = "<?php echo ( $images_path . 'check.png' ); ?>";
	MPv1.paths.error = "<?php echo ( $images_path . 'error.png' ); ?>";

	// Overriding this function to give form padding attribute.
	MPv1.setForm = function() {
		if ( MPv1.customer_and_card.status ) {
			document.querySelector( MPv1.selectors.form ).style.display = "none";
			document.querySelector( MPv1.selectors.mpSecurityCodeCustomerAndCard ).removeAttribute( "style" );
		} else {
			document.querySelector( MPv1.selectors.mpSecurityCodeCustomerAndCard ).style.display = "none";
			document.querySelector( MPv1.selectors.form ).removeAttribute( "style" );
		}
		Mercadopago.clearSession();
		if ( MPv1.create_token_on.event ) {
			MPv1.createTokenByEvent();
			MPv1.validateInputsCreateToken();
		}
		document.querySelector( MPv1.selectors.CustomerAndCard ).value =
			MPv1.customer_and_card.status;
	}

	MPv1.getAmount = function() {
		return document.querySelector( MPv1.selectors.amount )
		.value - document.querySelector( MPv1.selectors.discount ).value;
	}

	MPv1.getAmountWithoutDiscount = function() {
		return document.querySelector( MPv1.selectors.amount ).value;
	}

	MPv1.showErrors = function( response ) {
		var $form = MPv1.getForm();
		for ( var x=0; x<response.cause.length; x++ ) {
			var error = response.cause[x];
			var $span = $form.querySelector( "#mp-error-" + error.code );
			var $input = $form.querySelector( $span.getAttribute( "data-main" ) );
			$span.style.display = "inline-block";
			$input.classList.add( "mp-error-input" );
		}
		return;
	}
	MPv1.Initialize(
		mercadopago_site_id,
		mercadopago_public_key,
		mercadopago_coupon_mode == "yes",
		mercadopago_discount_action_url,
		mercadopago_payer_email
	);

</script>
