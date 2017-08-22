<?php

/**
 * Part of Woo Mercado Pago Module
 * Author - Mercado Pago
 * Developer - Marcelo Tomio Hama / marcelo.hama@mercadolivre.com
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

/**
 * Summary: Extending from WooCommerce Payment Gateway class.
 * Description: This class implements Mercado Pago Basic checkout.
 * @since 3.0.0
 */
class WC_WooMercadoPago_JSGen {

	public function __construct() {}

	public static function generate_refund_cancel_subscription( $domain, $success_msg, $fail_msg, $options, $str1, $str2, $str3, $str4 ) {
		$subscription_js = '<script type="text/javascript">
			( function() {
				var MPSubscription = {}
				MPSubscription.callSubscriptionCancel = function () {
					var url = "' . $domain . '";
					url += "&action_mp_payment_id=" + document.getElementById("payment_id").value;
					url += "&action_mp_payment_amount=" + document.getElementById("payment_amount").value;
					url += "&action_mp_payment_action=cancel";
					document.getElementById("sub_pay_cancel_btn").disabled = true;
					MPSubscription.AJAX({
						url: url,
						method : "GET",
						timeout : 5000,
						error: function() {
							document.getElementById("sub_pay_cancel_btn").disabled = false;
							alert("' . $fail_msg . '");
						},
						success : function ( status, data ) {
							document.getElementById("sub_pay_cancel_btn").disabled = false;
							var mp_status = data.status;
							var mp_message = data.message;
							if (data.status == 200) {
								alert("' . $success_msg . '");
							} else {
								alert(mp_message);
							}
						}
					});
				}
				MPSubscription.callSubscriptionRefund = function () {
					var url = "' . $domain . '";
					url += "&action_mp_payment_id=" + document.getElementById("payment_id").value;
					url += "&action_mp_payment_amount=" + document.getElementById("payment_amount").value;
					url += "&action_mp_payment_action=refund";
					document.getElementById("sub_pay_refund_btn").disabled = true;
					MPSubscription.AJAX({
						url: url,
						method : "GET",
						timeout : 5000,
						error: function() {
							document.getElementById("sub_pay_refund_btn").disabled = false;
							alert("' . $fail_msg . '");
						},
						success : function ( status, data ) {
							document.getElementById("sub_pay_refund_btn").disabled = false;
							var mp_status = data.status;
							var mp_message = data.message;
							if (data.status == 200) {
								alert("' . $success_msg . '");
							} else {
								alert(mp_message);
							}
						}
					});
				}
				MPSubscription.AJAX = function( options ) {
					var useXDomain = !!window.XDomainRequest;
					var req = useXDomain ? new XDomainRequest() : new XMLHttpRequest()
					var data;
					options.url += ( options.url.indexOf( "?" ) >= 0 ? "&" : "?" );
					options.requestedMethod = options.method;
					if ( useXDomain && options.method == "PUT" ) {
						options.method = "POST";
						options.url += "&_method=PUT";
					}
					req.open( options.method, options.url, true );
					req.timeout = options.timeout || 1000;
					if ( window.XDomainRequest ) {
						req.onload = function() {
							data = JSON.parse( req.responseText );
							if ( typeof options.success === "function" ) {
								options.success( options.requestedMethod === "POST" ? 201 : 200, data );
							}
						};
						req.onerror = req.ontimeout = function() {
							if ( typeof options.error === "function" ) {
								options.error( 400, {
									user_agent:window.navigator.userAgent, error : "bad_request", cause:[]
								});
							}
						};
						req.onprogress = function() {};
					} else {
						req.setRequestHeader( "Accept", "application/json" );
						if ( options.contentType ) {
							req.setRequestHeader( "Content-Type", options.contentType );
						} else {
							req.setRequestHeader( "Content-Type", "application/json" );
						}
						req.onreadystatechange = function() {
							if ( this.readyState === 4 ) {
								if ( this.status >= 200 && this.status < 400 ) {
									// Success!
									data = JSON.parse( this.responseText );
									if ( typeof options.success === "function" ) {
										options.success( this.status, data );
									}
								} else if ( this.status >= 400 ) {
									data = JSON.parse( this.responseText );
									if ( typeof options.error === "function" ) {
										options.error( this.status, data );
									}
								} else if ( typeof options.error === "function" ) {
									options.error( 503, {} );
								}
							}
						};
					}
					if ( options.method === "GET" || options.data == null || options.data == undefined ) {
						req.send();
					} else {
						req.send( JSON.stringify( options.data ) );
					}
				}
				this.MPSubscription = MPSubscription;
			} ).call();
		</script>';
		$subscription_meta_box = '<table>' .
			'<tr class="total">' .
				'<td><label for="payment_id" style="margin-right:1px;">' .
					$str1 .
				'</label></td>' .
				'<td><select id="payment_id" name="refund_payment_id" style="margin-left:1px;">' .
					$options .
				'</select></td>' .
			'</tr>' .
			'<tr class="total">' .
				'<td><label for="payment_amount" style="margin-right:1px;">' .
					$str2 .
				'</label></td>' .
				'<td><input type="number" class="text amount_input" id="payment_amount" value="0" name="payment_amount"' .
					' placeholder="Decimal" min="0" step="0.01" value="0.00" style="width:117px; margin-left:1px;"' .
					' ng-pattern="/^[0-9]+(\.[0-9]{1,2})?$/"/>' .
				'</td>' .
			'</tr>' .
			'<tr class="total">' .
				'<td><input onclick="MPSubscription.callSubscriptionRefund();" type="button"' .
					' id="sub_pay_refund_btn" class="button button-primary" style="margin-left:1px; margin-top:2px;"' .
					' name="refund" value="' . $str3 .
					'" style="margin-right:1px;"></td>' .
				'<td><input onclick="MPSubscription.callSubscriptionCancel();" type="button"' .
					' id="sub_pay_cancel_btn" class="button button-primary" style="margin-right:1px; margin-top:2px;"' .
					' name="cancel" value="' . $str4 .
					'" style="margin-left:1px;"></td>' .
			'</tr>' .
		'</table>';
		return $subscription_js . $subscription_meta_box;
	}

}