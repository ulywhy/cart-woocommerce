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

<div width="100%" class="mp-line" style="height:72px; margin-bottom:2px; padding:20px 36px 8px 36px; background:white;">
	<div class="mp-box-inputs mp-col-50">
		<img class="logo" src="<?php echo ($images_path . 'mplogo.png'); ?>" width="156" height="40" />
	</div>
	<div class="mp-box-inputs mp-col-50">
		<?php if ( count( $payment_methods ) > 1 ) : ?>
			<img class="logo" src="<?php echo ($images_path . 'boleto.png'); ?>"
			width="90" height="40" style="float:right;"/>
		<?php else : ?>
			<?php foreach ( $payment_methods as $payment ) : ?>
				<img class="logo" src="<?php echo $payment['secure_thumbnail']; ?>" width="67" height="30"
				style="float:right;"/>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>

<fieldset id="ticket_checkout_fieldset" style="margin:0px; background:white; display: none;">

	<!-- coupom -->
	<div class="mp-box-inputs mp-line form-row" id="mercadopago-form-coupon-ticket" style="padding:0px 12px 16px 12px;" >
		<div class="form-col-8">
			<label for="couponCodeLabel"><?php echo esc_html__( 'Discount Coupon', 'woocommerce-mercadopago' ); ?></label>
			<input type="text" id="couponCodeTicket" name="mercadopago_ticket[coupon_code]"
				autocomplete="off" maxlength="24" style="margin-bottom: 8px;"/>
			<span class="mp-discount" id="mpCouponApplyedTicket" ></span>
			<span class="mp-error" id="mpCouponErrorTicket" ></span>
		</div>
		<div class="form-col-4">
			<label >&nbsp;</label>
			<input type="button" class="button" id="applyCouponTicket" value="<?php echo esc_html__( 'Apply', 'woocommerce-mercadopago' ); ?>">
		</div>
	</div>

	<!-- payment method -->
	<div id="mercadopago-form-ticket" class="mp-box-inputs mp-line" style="padding:0px 12px 0px 12px;">
		<div id="form-ticket">
			<div class="form-row" style="margin-bottom:16px;">
				<div class="form-col-1"> </div>
				<div class="form-col-4">
					<input type="radio" name="mercadopago_ticket[docType]" class="MPv1Ticket-docType"
						id="MPv1Ticket-docType-fisica" value="CPF" style="width:24px; height:24px;" checked="checked">
						<?php echo esc_html__( 'Fisical Person', 'woocommerce-mercadopago' ); ?>
					</input>
				</div>
				<div class="form-col-2"> </div>
				<div class="form-col-4">
					<input type="radio" name="mercadopago_ticket[docType]" class="MPv1Ticket-docType"
						id="MPv1Ticket-docType-juridica" value="CNPJ" style="width:24px; height:24px;">
						<?php echo esc_html__( 'Legal Person', 'woocommerce-mercadopago' ); ?>
					</input>
				</div>
				<div class="form-col-1"> </div>
			</div>
			<div class="form-row">
				<div class="form-col-4" id="box-firstname">
					<label for="firstname" class="title-name"><?php echo esc_html__( 'NAME', 'woocommerce-mercadopago' ); ?><em class="obrigatorio"> *</em></label>
					<label for="firstname" class="title-razao-social"><?php echo esc_html__( 'SOCIAL NAME', 'woocommerce-mercadopago' ); ?><em class="obrigatorio"> *</em></label>
					<input type="text" value="<?php echo $febraban['firstname']; ?>"
						id="firstname" class="form-control-mine" name="mercadopago_ticket[firstname]">
					<span class="erro_febraban" data-main="#firstname" id="error_firstname"><?php echo esc_html__( 'You must inform you NAME', 'woocommerce-mercadopago' ); ?></span>
				</div>
				<div class="form-col-4" id="box-lastname">
					<label for="lastname"><?php echo esc_html__( 'SURNAME', 'woocommerce-mercadopago' ); ?><em class="obrigatorio"> *</em></label>
					<input type="text" value="<?php echo $febraban['lastname']; ?>"
						id="lastname" class="form-control-mine" name="mercadopago_ticket[lastname]">
					<span class="erro_febraban" data-main="#lastname" id="error_lastname"><?php echo esc_html__( 'You must inform your SURNAME', 'woocommerce-mercadopago' ); ?></span>
				</div>
				<div class="form-col-4" id="box-docnumber">
					<label for="cpfcnpj" class="title-cpf"><?php echo esc_html__( 'DOCUMENT', 'woocommerce-mercadopago' ); ?><em class="obrigatorio"> *</em></label>
					<label for="cpfcnpj" class="title-cnpj"><?php echo esc_html__( 'CNPJ', 'woocommerce-mercadopago' ); ?><em class="obrigatorio"> *</em></label>
					<input type="text" value="<?php echo $febraban['docNumber']; ?>"
						id="cpfcnpj" class="form-control-mine" name="mercadopago_ticket[docNumber]" maxlength="14">
					<span class="erro_febraban" data-main="#cpfcnpj" id="error_docNumber"><?php echo esc_html__( 'You must inform your DOCUMENT', 'woocommerce-mercadopago' ); ?></span>
				</div>
			</div>
			<div class="form-row">
				<div class="form-col-8">
					<label for="address"><?php echo esc_html__( 'ADDRESS', 'woocommerce-mercadopago' ); ?><em class="obrigatorio"> *</em></label>
					<input type="text" value="<?php echo $febraban['address']; ?>"
						id="address" class="form-control-mine" name="mercadopago_ticket[address]">
					<span class="erro_febraban" data-main="#address" id="error_address"><?php echo esc_html__( 'You must inform your ADDRESS', 'woocommerce-mercadopago' ); ?></span>
				</div>
				<div class="form-col-4">
					<label for="number"><?php echo esc_html__( 'NUMBER', 'woocommerce-mercadopago' ); ?><em class="obrigatorio"> *</em></label>
					<input type="text" value="<?php echo $febraban['number']; ?>"
						id="number" class="form-control-mine" name="mercadopago_ticket[number]">
					<span class="erro_febraban" data-main="#number" id="error_number"><?php echo esc_html__( 'You must inform your ADDRESS NUMBER', 'woocommerce-mercadopago' ); ?></span>
				</div>
			</div>
			<div class="form-row">
				<div class="form-col-4">
					<label for="city"><?php echo esc_html__( 'CITY', 'woocommerce-mercadopago' ); ?><em class="obrigatorio"> *</em></label>
					<input type="text" value="<?php echo $febraban['city']; ?>"
						id="city" class="form-control-mine" name="mercadopago_ticket[city]">
					<span class="erro_febraban" data-main="#city" id="error_city"><?php echo esc_html__( 'You must inform your CITY', 'woocommerce-mercadopago' ); ?></span>
				</div>
				<div class="form-col-4">
					<label for="state"><?php echo esc_html__( 'STATE', 'woocommerce-mercadopago' ); ?><em class="obrigatorio"> *</em></label>
					<select name="mercadopago_ticket[state]" id="state" class="form-control-mine" style="width: 100%;">
						<option value="" <?php if ($febraban['state'] == '') {echo 'selected="selected"';} ?>><?php echo esc_html__( 'Choose', 'woocommerce-mercadopago' ); ?></option>
						<option value="AC" <?php if ($febraban['state'] == 'AC') {echo 'selected="selected"';} ?>>Acre</option>
						<option value="AL" <?php if ($febraban['state'] == 'AL') {echo 'selected="selected"';} ?>>Alagoas</option>
						<option value="AP" <?php if ($febraban['state'] == 'AP') {echo 'selected="selected"';} ?>>Amapá</option>
						<option value="AM" <?php if ($febraban['state'] == 'AM') {echo 'selected="selected"';} ?>>Amazonas</option>
						<option value="BA" <?php if ($febraban['state'] == 'BA') {echo 'selected="selected"';} ?>>Bahia</option>
						<option value="CE" <?php if ($febraban['state'] == 'CE') {echo 'selected="selected"';} ?>>Ceará</option>
						<option value="DF" <?php if ($febraban['state'] == 'DF') {echo 'selected="selected"';} ?>>Distrito Federal</option>
						<option value="ES" <?php if ($febraban['state'] == 'ES') {echo 'selected="selected"';} ?>>Espírito Santo</option>
						<option value="GO" <?php if ($febraban['state'] == 'GO') {echo 'selected="selected"';} ?>>Goiás</option>
						<option value="MA" <?php if ($febraban['state'] == 'MA') {echo 'selected="selected"';} ?>>Maranhão</option>
						<option value="MT" <?php if ($febraban['state'] == 'MT') {echo 'selected="selected"';} ?>>Mato Grosso</option>
						<option value="MS" <?php if ($febraban['state'] == 'MS') {echo 'selected="selected"';} ?>>Mato Grosso do Sul</option>
						<option value="MG" <?php if ($febraban['state'] == 'MG') {echo 'selected="selected"';} ?>>Minas Gerais</option>
						<option value="PA" <?php if ($febraban['state'] == 'PA') {echo 'selected="selected"';} ?>>Pará</option>
						<option value="PB" <?php if ($febraban['state'] == 'PB') {echo 'selected="selected"';} ?>>Paraíba</option>
						<option value="PR" <?php if ($febraban['state'] == 'PR') {echo 'selected="selected"';} ?>>Paraná</option>
						<option value="PE" <?php if ($febraban['state'] == 'PE') {echo 'selected="selected"';} ?>>Pernambuco</option>
						<option value="PI" <?php if ($febraban['state'] == 'PI') {echo 'selected="selected"';} ?>>Piauí</option>
						<option value="RJ" <?php if ($febraban['state'] == 'RJ') {echo 'selected="selected"';} ?>>Rio de Janeiro</option>
						<option value="RN" <?php if ($febraban['state'] == 'RN') {echo 'selected="selected"';} ?>>Rio Grande do Norte</option>
						<option value="RS" <?php if ($febraban['state'] == 'RS') {echo 'selected="selected"';} ?>>Rio Grande do Sul</option>
						<option value="RO" <?php if ($febraban['state'] == 'RO') {echo 'selected="selected"';} ?>>Rondônia</option>
						<option value="RA" <?php if ($febraban['state'] == 'RA') {echo 'selected="selected"';} ?>>Roraima</option>
						<option value="SC" <?php if ($febraban['state'] == 'SC') {echo 'selected="selected"';} ?>>Santa Catarina</option>
						<option value="SP" <?php if ($febraban['state'] == 'SP') {echo 'selected="selected"';} ?>>São Paulo</option>
						<option value="SE" <?php if ($febraban['state'] == 'SE') {echo 'selected="selected"';} ?>>Sergipe</option>
						<option value="TO" <?php if ($febraban['state'] == 'TO') {echo 'selected="selected"';} ?>>Tocantins</option>
					</select>
					<span class="erro_febraban" data-main="#state" id="error_state"><?php echo esc_html__( 'You must inform your STATE', 'woocommerce-mercadopago' ); ?></span>
				</div>
				<div class="form-col-4">
					<label for="zipcode"><?php echo esc_html__( 'ZIP', 'woocommerce-mercadopago' ); ?><em class="obrigatorio"> *</em></label>
					<input type="text" value="<?php echo $febraban['zipcode']; ?>"
						id="zipcode" class="form-control-mine" name="mercadopago_ticket[zipcode]"
						onkeydown="return (event.which >= 48 && event.which <= 57) || event.which == 8 || event.which == 46">
					<span class="erro_febraban" data-main="#zipcode" id="error_zipcode"><?php echo esc_html__( 'You must inform your ZIP', 'woocommerce-mercadopago' ); ?></span>
				</div>
			</div>
			<div class="form-col-12">
				<label>
					<span class="mensagem-febraban"><em class="obrigatorio">* </em><?php echo esc_html__( 'Needed informations due to brazilian bank compliances numbers 3.461/09, 3.598/12 and 3.656/13 of the Central Bank of Brazil.', 'woocommerce-mercadopago' ); ?></span>
				</label>
			</div>
		</div>

		<div style="padding:0px 36px 0px 36px; margin-left: -32px; margin-right: -32px;">
			<p>
				<?php
					if ( count( $payment_methods ) > 1 ) :
						echo esc_html__( 'Please, select the ticket issuer of your preference.', 'woocommerce-mercadopago' );
					endif;
					echo esc_html__( 'Click [Place order] button. The ticket will be generated and you will be redirected to print it.', 'woocommerce-mercadopago' );
				?>&nbsp;<?php
					echo esc_html__( 'Important: The order will be confirmed only after the payment approval.', 'woocommerce-mercadopago' );
					if ( $currency_ratio != 1 ) :
	  					echo ' (' . esc_html__( 'Payment converted from', 'woocommerce-mercadopago' ) . ' ' .
						$woocommerce_currency . ' ' . esc_html__( 'to', 'woocommerce-mercadopago' ) . ' ' .
						$account_currency . ')';
					endif;
				?>
			</p>
			<?php if ( count( $payment_methods ) > 1 ) : ?>
				<div class="mp-box-inputs mp-col-100" >
					<?php $atFirst = true; ?>
					<?php foreach ( $payment_methods as $payment ) : ?>
						<div class="mp-box-inputs mp-line">
							<div id="paymentMethodIdTicket" class="mp-box-inputs mp-col-5">
								<input type="radio" class="input-radio" name="mercadopago_ticket[paymentMethodId]"
									style="display: block; height:16px; width:16px;" value="<?php echo $payment['id']; ?>"
								<?php if ( $atFirst ) : ?> checked="checked" <?php endif; ?> />
							</div>
							<div class="mp-box-inputs mp-col-75">
								<label>
									&nbsp;
									<img src="<?php echo $payment['secure_thumbnail']; ?>"
									alt="<?php echo $payment['name']; ?>" />
									&nbsp;
									<?php echo $payment['name']; ?>
								</label>
							</div>
						</div>
						<?php $atFirst = false; ?>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="mp-box-inputs mp-col-100" style="display:none;">
					<select id="paymentMethodIdTicket" name="mercadopago_ticket[paymentMethodId]">
						<?php foreach ( $payment_methods as $payment ) : ?>
							<option value="<?php echo $payment['id']; ?>" style="padding: 8px;
							background: url('https://img.mlstatic.com/org-img/MP3/API/logos/bapropagos.gif')
							98% 50% no-repeat;"> <?php echo $payment['name']; ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>

			<div class="mp-box-inputs mp-line">
				<div class="mp-box-inputs mp-col-25">
					<div id="mp-box-loading">
					</div>
				</div>
			</div>

			<!-- utilities -->
			<div class="mp-box-inputs mp-col-100" id="mercadopago-utilities">
				<input type="hidden" id="site_id" value="<?php echo $site_id; ?>" name="mercadopago_ticket[site_id]"/>
				<input type="hidden" id="amountTicket" value="<?php echo $amount; ?>" name="mercadopago_ticket[amount]"/>
				<input type="hidden" id="currency_ratioTicket" value="<?php echo $currency_ratio; ?>" name="mercadopago_ticket[currency_ratio]"/>
				<input type="hidden" id="campaign_idTicket" name="mercadopago_ticket[campaign_id]"/>
				<input type="hidden" id="campaignTicket" name="mercadopago_ticket[campaign]"/>
				<input type="hidden" id="discountTicket" name="mercadopago_ticket[discount]"/>
			</div>

		</div>
	</div>
</fieldset>

<script type="text/javascript" src="<?php echo $path_to_javascript; ?>"/>

<script type="text/javascript">
	MPv1Ticket.text.apply = "<?php echo __( 'Apply', 'woocommerce-mercadopago' ); ?>";
	MPv1Ticket.text.remove = "<?php echo __( 'Remove', 'woocommerce-mercadopago' ); ?>";
	MPv1Ticket.text.coupon_empty = "<?php echo __( 'Please, inform your coupon code', 'woocommerce-mercadopago' ); ?>";
	MPv1Ticket.text.discount_info1 = "<?php echo __( 'You will save', 'woocommerce-mercadopago' ); ?>";
	MPv1Ticket.text.discount_info2 = "<?php echo __( 'with discount from', 'woocommerce-mercadopago' ); ?>";
	MPv1Ticket.text.discount_info3 = "<?php echo __( 'Total of your purchase:', 'woocommerce-mercadopago' ); ?>";
	MPv1Ticket.text.discount_info4 = "<?php echo __( 'Total of your purchase with discount:', 'woocommerce-mercadopago' ); ?>";
	MPv1Ticket.text.discount_info5 = "<?php echo __( '*Uppon payment approval', 'woocommerce-mercadopago' ); ?>";
	MPv1Ticket.text.discount_info6 = "<?php echo __( 'Terms and Conditions of Use', 'woocommerce-mercadopago' ); ?>";
	
	MPv1Ticket.paths.loading = "<?php echo ( $images_path . 'loading.gif' ); ?>";
	MPv1Ticket.paths.check = "<?php echo ( $images_path . 'check.png' ); ?>";
	MPv1Ticket.paths.error = "<?php echo ( $images_path . 'error.png' ); ?>";

	MPv1Ticket.Initialize(
		"<?php echo $site_id; ?>",
		"<?php echo $coupon_mode; ?>" == "yes",
		"<?php echo $discount_action_url; ?>",
		"<?php echo $payer_email; ?>"
	);

	document.querySelector( "#ticket_checkout_fieldset" ).style.display = "block";
</script>
