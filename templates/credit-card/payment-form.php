<?php

/**
 * Part of Woo Mercado Pago Module
 * Author - Mercado Pago
 * Developer - Marcelo Tomio Hama / marcelo.hama@mercadolivre.com
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

if (!defined('ABSPATH')) {
    exit;
}

?>

<?php
$theme = wp_get_theme(); // gets the current theme
if ('Twenty Seventeen' == $theme->name || 'Twenty Seventeen' == $theme->parent_theme) {
    echo '<div class="mp-line twenty-seventeen-cc-header" >';
} else {
    echo '<div class="mp-line other-themes-cc-header" >';
}
?>
<?php if (!empty($banner_path)) : ?>
    <img class="mp-creditcard-banner" src="<?php echo $banner_path; ?>" width="312" height="40"/>
<?php endif; ?>
</div>

<fieldset id="custom_checkout_fieldset" style="margin:-1px; background:white; display: none;">

    <div class="mp-box-inputs mp-line" id="mercadopago-form-coupon" style="padding: 0px 12px 0px 12px;">
        <label for="couponCodeLabel">
            <?php echo esc_html__('Discount Coupon', 'woocommerce-mercadopago'); ?>
        </label>
        <div class="mp-box-inputs mp-col-55">
            <input type="text" id="couponCode" name="mercadopago_custom[coupon_code]"
                   autocomplete="off" maxlength="24"
                   style="background: #fff; padding: 12px; border: 1px solid #cecece;"/>
        </div>
        <div class="mp-box-inputs mp-col-10">
            <div id="mp-separete-date"></div>
        </div>
        <div class="mp-box-inputs mp-col-35">
            <input type="button" class="button" id="applyCoupon"
                   value="<?php echo esc_html__('Apply', 'woocommerce-mercadopago'); ?>">
        </div>
        <div class="mp-box-inputs mp-col-65 mp-box-message" style="margin-top:2px;">
            <span class="mp-discount" id="mpCouponApplyed"></span>
            <span class="mp-error" id="mpCouponError"></span>
        </div>
    </div>

    <!-- payment method -->
    <div id="mercadopago-form-customer-and-card" style="padding:0px 12px 0px 12px;">
        <div class="mp-box-inputs mp-line">
            <label for="paymentMethodIdSelector">
                <?php echo esc_html__('Payment Method', 'woocommerce-mercadopago'); ?> <em>*</em>
            </label>
            <select id="paymentMethodSelector" name="mercadopago_custom[paymentMethodSelector]"
                    data-checkout="cardId">
                <optgroup label=<?php echo esc_html__('Your Card', 'woocommerce-mercadopago'); ?>
                          id="payment-methods-for-customer-and-cards">
                    <?php foreach ($customer_cards as $card) : ?>
                        <option value=<?php echo $card['id']; ?>
                                first_six_digits=<?php echo $card['first_six_digits']; ?>
                                last_four_digits=<?php echo $card['last_four_digits']; ?>
                                security_code_length=<?php echo $card['security_code']['length']; ?>
                                type_checkout='customer_and_card'
                                payment_method_id=<?php echo $card['payment_method']['id']; ?>>
                            <?php echo ucfirst($card['payment_method']['name']); ?>
                            <?php echo esc_html__('ended in', 'woocommerce-mercadopago'); ?>
                            <?php echo $card['last_four_digits']; ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
                <optgroup label="<?php echo esc_html__('Other Cards', 'woocommerce-mercadopago'); ?>"
                          id="payment-methods-list-other-cards">
                    <option value="-1"><?php echo esc_html__('Other Card', 'woocommerce-mercadopago'); ?></option>
                </optgroup>
            </select>
        </div>
        <div class="mp-box-inputs mp-line" id="mp-securityCode-customer-and-card">
            <div class="mp-box-inputs mp-col-45">
                <label for="customer-and-card-securityCode">
                    <?php echo esc_html__('CVC', 'woocommerce-mercadopago'); ?> <em>*</em>
                </label>
                <input type="text" id="customer-and-card-securityCode" data-checkout="securityCode"
                       autocomplete="off" maxlength="4" style="padding: 8px; border: 1px solid #cecece;
                        background: url( <?php echo($images_path . 'cvv.png'); ?> ) 94% 50% no-repeat;"/>
                <span class="mp-error" id="mp-error-224" data-main="#customer-and-card-securityCode">
					<?php echo esc_html__('Parameter securityCode can not be null/empty', 'woocommerce-mercadopago'); ?>
				</span>
                <span class="mp-error" id="mp-error-E302" data-main="#customer-and-card-securityCode">
					<?php echo esc_html__('Invalid Security Code', 'woocommerce-mercadopago'); ?>
				</span>
                <span class="mp-error" id="mp-error-E203" data-main="#customer-and-card-securityCode">
					<?php echo esc_html__('Invalid Security Code', 'woocommerce-mercadopago'); ?>
				</span>
            </div>
        </div>
    </div> <!--  end mercadopago-form-osc -->

    <div id="mercadopago-form" style="padding:0px 12px 0px 12px;">
        <!-- Card Number -->
        <div class="mp-box-inputs mp-col-100">
            <label for="cardNumber">
                <?php echo esc_html__('Credit card number', 'woocommerce-mercadopago'); ?> <em>*</em>
            </label>
            <input type="text" id="cardNumber" data-checkout="cardNumber" autocomplete="off"
                   maxlength="19" style="background: #fff; padding: 8px; border: 1px solid #cecece;"/>
            <span class="mp-error" id="mp-error-205" data-main="#cardNumber">
				<?php echo esc_html__('Parameter cardNumber can not be null/empty', 'woocommerce-mercadopago'); ?>
			</span>
            <span class="mp-error" id="mp-error-E301" data-main="#cardNumber">
				<?php echo esc_html__('Invalid Card Number', 'woocommerce-mercadopago'); ?>
			</span>
        </div>
        <!-- Expiry Date -->
        <div class="mp-box-inputs mp-line">
            <div class="mp-box-inputs mp-col-45">
                <label for="cardExpirationMonth">
                    <?php echo esc_html__('Expiration month', 'woocommerce-mercadopago'); ?> <em>*</em>
                </label>
                <select id="cardExpirationMonth" data-checkout="cardExpirationMonth"
                        name="mercadopago_custom[cardExpirationMonth]">
                    <option value="-1"> <?php echo esc_html__('Month', 'woocommerce-mercadopago'); ?> </option>
                    <?php for ($x = 1; $x <= 12; $x++) : ?>
                        <option value="<?php echo $x; ?>"> <?php echo $x; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="mp-box-inputs mp-col-10">
                <div id="mp-separete-date"></div>
            </div>
            <div class="mp-box-inputs mp-col-45">
                <label for="cardExpirationYear">
                    <?php echo esc_html__('Expiration year', 'woocommerce-mercadopago'); ?> <em>*</em>
                </label>
                <select id="cardExpirationYear" data-checkout="cardExpirationYear"
                        name="mercadopago_custom[cardExpirationYear]">
                    <option value="-1"> <?php echo esc_html__('Year', 'woocommerce-mercadopago'); ?> </option>
                    <?php for ($x = date("Y"); $x <= date("Y") + 10; $x++) : ?>
                        <option value="<?php echo $x; ?>"> <?php echo $x; ?> </option>
                    <?php endfor; ?>
                </select>
            </div>
            <span class="mp-error" id="mp-error-208" data-main="#cardExpirationMonth">
				<?php echo esc_html__('Invalid Expiration Date', 'woocommerce-mercadopago'); ?>
			</span>
            <span class="mp-error" id="mp-error-209" data-main="#cardExpirationYear"> </span>
            <span class="mp-error" id="mp-error-325" data-main="#cardExpirationMonth">
				<?php echo esc_html__('Invalid Expiration Date', 'woocommerce-mercadopago'); ?>
			</span>
            <span class="mp-error" id="mp-error-326" data-main="#cardExpirationYear"> </span>
        </div>
        <!-- Card Holder Name -->
        <div class="mp-box-inputs mp-col-100">
            <label for="cardholderName">
                <?php echo esc_html__('Card holder name', 'woocommerce-mercadopago'); ?> <em>*</em>
            </label>
            <input type="text" id="cardholderName" name="mercadopago_custom[cardholderName]"
                   data-checkout="cardholderName" autocomplete="off"
                   style="background-color: #fff; border: 1px solid #cecece;"/>
            <span class="mp-error" id="mp-error-221" data-main="#cardholderName">
				<?php echo esc_html__('Parameter cardholderName can not be null/empty', 'woocommerce-mercadopago'); ?>
			</span>
            <span class="mp-error" id="mp-error-316" data-main="#cardholderName">
				<?php echo esc_html__('Invalid Card Holder Name', 'woocommerce-mercadopago'); ?>
			</span>
        </div>
        <!-- CVV -->
        <div class="mp-box-inputs mp-line">
            <div class="mp-box-inputs mp-col-45">
                <label for="securityCode">
                    <?php echo esc_html__('CVC', 'woocommerce-mercadopago'); ?> <em>*</em>
                </label>
                <input type="text" id="securityCode" data-checkout="securityCode"
                       autocomplete="off" maxlength="4" style="padding: 12px; border: 1px solid #cecece;
                        background: url(<?php echo($images_path . 'cvv.png'); ?>) 94% 50% no-repeat;"/>
                <span class="mp-error" id="mp-error-224" data-main="#securityCode">
					<?php echo esc_html__('Parameter securityCode can not be null/empty', 'woocommerce-mercadopago'); ?>
				</span>
                <span class="mp-error" id="mp-error-E302" data-main="#securityCode">
					<?php echo esc_html__('Invalid Security Code', 'woocommerce-mercadopago'); ?>
				</span>
            </div>
            <div class="mp-box-inputs mp-col-10">
                <div id="mp-separete-date"></div>
            </div>
            <!-- Issuer -->
            <div class="mp-box-inputs mp-col-45 mp-issuer">
                <label for="issuer">
                    <?php echo esc_html__('Issuer', 'woocommerce-mercadopago'); ?> <em>*</em>
                </label>
                <select id="issuer" data-checkout="issuer" name="mercadopago_custom[issuer]"></select>
                <span class="mp-error" id="mp-error-220" data-main="#issuer">
					<?php echo esc_html__('Parameter cardIssuerId can not be null/empty', 'woocommerce-mercadopago'); ?>
				</span>
            </div>
            <?php if ($site_id == 'MLB') : ?>
                <div class="mp-box-inputs mp-col-45 mp-docNumber">
                    <label for="docNumber">
                        <?php echo esc_html__('Document number', 'woocommerce-mercadopago'); ?> <em>*</em>
                    </label>
                    <input type="text" id="docNumber" data-checkout="docNumber"
                           name="mercadopago_custom[docNumber]" autocomplete="off"
                           style="background-color: #fff; border: 1px solid #cecece;"/>
                    <span class="mp-error" id="mp-error-214" data-main="#docNumber">
						<?php echo esc_html__('Parameter docNumber can not be null/empty', 'woocommerce-mercadopago'); ?>
					</span>
                    <span class="mp-error" id="mp-error-324" data-main="#docNumber">
						<?php echo esc_html__('Invalid Document Number', 'woocommerce-mercadopago'); ?>
					</span>
                </div>
            <?php endif; ?>
        </div>
        <!-- Document Type -->
        <div id="mp-doc-div" class="mp-box-inputs mp-col-100 mp-doc">
            <?php if ($site_id != 'MLB') : ?>
                <div class="mp-box-inputs mp-col-45 mp-docNumber">
                    <label for="docNumber">
                        <?php echo esc_html__('Document number', 'woocommerce-mercadopago'); ?> <em>*</em>
                    </label>
                    <input type="text" id="docNumber" data-checkout="docNumber"
                           name="mercadopago_custom[docNumber]" autocomplete="off"
                           style="background-color: #fff; border: 1px solid #cecece;"/>
                    <span class="mp-error" id="mp-error-214" data-main="#docNumber">
						<?php echo esc_html__('Parameter docNumber can not be null/empty', 'woocommerce-mercadopago'); ?>
					</span>
                    <span class="mp-error" id="mp-error-324" data-main="#docNumber">
						<?php echo esc_html__('Invalid Document Number', 'woocommerce-mercadopago'); ?>
					</span>
                </div>
            <?php endif; ?>
            <div class="mp-box-inputs mp-col-10">
                <div id="mp-separete-date"></div>
            </div>
            <div class="mp-box-inputs mp-col-45 mp-docType">
                <label for="docType">
                    <?php echo esc_html__('Document Type', 'woocommerce-mercadopago'); ?> <em>*</em>
                </label>
                <select id="docType" data-checkout="docType"
                        name="mercadopago_custom[docType]"></select>
                <span class="mp-error" id="mp-error-212" data-main="#docType">
					<?php echo esc_html__('Parameter docType can not be null/empty', 'woocommerce-mercadopago'); ?>
				</span>
                <span class="mp-error" id="mp-error-322" data-main="#docType">
					<?php echo esc_html__('Invalid Document Type', 'woocommerce-mercadopago'); ?>
				</span>
            </div>
        </div>
    </div> <!-- end #mercadopago-form -->

    <div id="mp-box-installments" class="mp-box-inputs mp-line">
        <div class="form-row">
            <div id="mp-box-installments-selector" class="form-col-12" style="padding: 0px 12px 0px 12px;">
                <label for="installments">
					<span class="mensagem-credit-card">
						<?php if ($currency_ratio != 1) : ?>
                            <div class="tooltip">
								<?php echo esc_html__('Installments', 'woocommerce-mercadopago'); ?>
								<span class="tooltiptext">
									<?php echo esc_html__('Payment converted from', 'woocommerce-mercadopago') . " " .
                                        $woocommerce_currency . " " . esc_html__('to', 'woocommerce-mercadopago') . " " .
                                        $account_currency; ?>
								</span>
							</div>
                        <?php else :
                            echo esc_html__('Installments', 'woocommerce-mercadopago');
                        endif; ?>
						<em class="obrigatorio">* </em>
					</span>
                </label>
                <select id="installments" data-checkout="installments" class="form-control-mine"
                        name="mercadopago_custom[installments]" style="width: 100%;"></select>
            </div>
        </div>
        <div id="mp-box-input-tax-cft" class="form-col-12" style="padding: 0px 12px 0px 12px;">
            <div id="mp-box-input-tax-tea">
                <div id="mp-tax-tea-text"></div>
            </div>
            <div id="mp-tax-cft-text"></div>
        </div>
    </div>

    <div style="padding:0px 12px 0px 12px;">
        <label for="saveCard" class="show_if_simple tips" style="display: inline;">
            <input type="checkbox" name="mercadopago_custom[doNotSaveCard]" id="doNotSaveCard" value="yes">
            <?php echo esc_html__('Do not save my card', 'woocommerce-mercadopago'); ?>
        </label>
    </div>

    <div class="mp-box-inputs mp-line" style="padding:0px 12px 0px 12px;">
        <!-- NOT DELETE LOADING-->
        <div class="mp-box-inputs mp-col-25">
            <div id="mp-box-loading"></div>
        </div>
    </div>

    <div id="mercadopago-utilities">
        <input type="hidden" id="site_id" name="mercadopago_custom[site_id]"/>
        <input type="hidden" id="amount" value='<?php echo $amount; ?>' name="mercadopago_custom[amount]"/>
        <input type="hidden" id="currency_ratio" value='<?php echo $currency_ratio; ?>'
               name="mercadopago_custom[currency_ratio]"/>
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
<script type="text/javascript" src="<?php echo $path_to_javascript; ?>"/>

<script type="text/javascript">
    MPv1.text.apply = "<?php echo __('Apply', 'woocommerce-mercadopago'); ?>";
    MPv1.text.remove = "<?php echo __('Remove', 'woocommerce-mercadopago'); ?>";
    MPv1.text.coupon_empty = "<?php echo __('Please, inform your coupon code', 'woocommerce-mercadopago'); ?>";
    MPv1.text.choose = "<?php echo __('Choose', 'woocommerce-mercadopago'); ?>";
    MPv1.text.other_bank = "<?php echo __('Other Bank', 'woocommerce-mercadopago'); ?>";
    MPv1.text.discount_info1 = "<?php echo __('You will save', 'woocommerce-mercadopago'); ?>";
    MPv1.text.discount_info2 = "<?php echo __('with discount from', 'woocommerce-mercadopago'); ?>";
    MPv1.text.discount_info3 = "<?php echo __('Total of your purchase:', 'woocommerce-mercadopago'); ?>";
    MPv1.text.discount_info4 = "<?php echo __('Total of your purchase with discount:', 'woocommerce-mercadopago'); ?>";
    MPv1.text.discount_info5 = "<?php echo __('*Uppon payment approval', 'woocommerce-mercadopago'); ?>";
    MPv1.text.discount_info6 = "<?php echo __('Terms and Conditions of Use', 'woocommerce-mercadopago'); ?>";
    MPv1.paths.loading = "<?php echo($images_path . 'loading.gif'); ?>";
    MPv1.paths.check = "<?php echo($images_path . 'check.png'); ?>";
    MPv1.paths.error = "<?php echo($images_path . 'error.png'); ?>";

    MPv1.Initialize(
        "<?php echo $site_id; ?>",
        "<?php echo $public_key; ?>",
        "<?php echo $coupon_mode; ?>" == "yes",
        "<?php echo $discount_action_url; ?>",
        "<?php echo $payer_email; ?>"
    );

    document.querySelector("#custom_checkout_fieldset").style.display = "block";
</script>

