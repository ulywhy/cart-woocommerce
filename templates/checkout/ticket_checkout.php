<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="panel-custom-checkout">
    <div class="row pb-20">
        <h2 class="title-checkout"><?=__('Pagar con Mercado Pago es fácil y seguro.', 'woocommerce-mercadopago')?></h2>

        <div class="col-md-12 mb-20">
            <div class="frame-tarjetas text-justify">
                <div id="mercadopago-form-coupon-ticket">
                    <p class="subtitle-custom-checkout"><?=__('Ingresa tu cupón de descuento', 'woocommerce-mercadopago')?></p>

                    <div class="row pt-10">
                        <div class="col-md-9 pr-15">
                            <input type="text" class="mp-form-control" id="couponCodeTicket" name="mercadopago_ticket[coupon_code]" autocomplete="off" maxlength="24" placeholder="<?=__('Ingresá tu cupón', 'woocommerce-mercadopago')?>" />
                        </div>

                        <div class="col-md-3">
                            <input type="button" class="mp-button pointer" id="applyCouponTicket" value="<?= esc_html__('Aplicar', 'woocommerce-mercadopago'); ?>">
                        </div>
                    </div>

                    <span class="mp-discount" id="mpCouponApplyedTicket"></span>
                    <span class="erro_febraban" id="mpCouponErrorTicket"><?=__('El código que ingresaste es incorrecto', 'woocommerce-mercadopago')?></span>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="frame-tarjetas">
                <div id="mercadopago-form-ticket">

                    <div id="form-ticket">
                        <div class="row pt-10">
                            <div class="col-md-6 pt-15">
                                <label for="MPv1Ticket-docType-fisica" class="mp-label-form-check pointer">
                                    <input type="radio" name="mercadopago_ticket[docType]" class="mp-form-control-check" id="MPv1Ticket-docType-fisica" value="CPF" checked="checked" />
                                    <?= esc_html__('Persona Física', 'woocommerce-mercadopago'); ?>
                                </label>
                            </div>

                            <div class="col-md-6">
                                <label for="MPv1Ticket-docType-juridica" class="mp-label-form-check pointer">
                                    <input type="radio" name="mercadopago_ticket[docType]" class="mp-form-control-check" id="MPv1Ticket-docType-juridica" value="CNPJ">
                                    <?= esc_html__('Persona Jurídica', 'woocommerce-mercadopago'); ?>
                                </label>
                            </div>
                        </div>

                        <div class="row pt-10">
                            <div class="col-md-4 pr-15" id="box-firstname">
                                <label for="firstname" class="mp-label-form title-name"><?= esc_html__('Nome', 'woocommerce-mercadopago'); ?> <em>*</em></label>
                                <label for="firstname" class="title-razao-social mp-label-form"><?= esc_html__('Razão social', 'woocommerce-mercadopago'); ?> <em>*</em></label>
                                <input type="text" class="mp-form-control" value="<?= $febraban['firstname']; ?>" id="firstname" name="mercadopago_ticket[firstname]">
                                <span class="erro_febraban" data-main="#firstname" id="error_firstname"><?= esc_html__('Debes informar tu nombre', 'woocommerce-mercadopago'); ?></span>
                            </div>

                            <div class="col-md-4 pr-15" id="box-lastname">
                                <label for="lastname" class="mp-label-form"><?= esc_html__('Apellido', 'woocommerce-mercadopago'); ?> <em>*</em></label>
                                <input type="text" class="mp-form-control" value="<?= $febraban['lastname']; ?>" id="lastname" name="mercadopago_ticket[lastname]">
                                <span class="erro_febraban" data-main="#lastname" id="error_lastname"><?= esc_html__('Debes informar tu apellido', 'woocommerce-mercadopago'); ?></span>
                            </div>

                            <div class="col-md-4" id="box-docnumber">
                                <label for="cpfcnpj" class="mp-label-form title-cpf"><?= esc_html__('CPF', 'woocommerce-mercadopago'); ?> <em>*</em></label>
                                <label for="cpfcnpj" class="title-cnpj mp-label-form"><?= esc_html__('CNPJ', 'woocommerce-mercadopago'); ?><em>*</em></label>
                                <input type="text" value="<?= $febraban['docNumber']; ?>" id="cpfcnpj" class="mp-form-control" name="mercadopago_ticket[docNumber]" maxlength="14">
                                <span class="erro_febraban" data-main="#cpfcnpj" id="error_docNumber"><?= esc_html__('Debe informar su número de documento', 'woocommerce-mercadopago'); ?></span>
                            </div>
                        </div>

                        <div class="row pt-10">
                            <div class="col-md-8 pr-15" id="box-firstname">
                                <label for="address" class="mp-label-form"><?= esc_html__('Dirección', 'woocommerce-mercadopago'); ?> <em>*</em></label>
                                <input type="text" value="<?= $febraban['address']; ?>" id="address" class="mp-form-control" name="mercadopago_ticket[address]">
                                <span class="erro_febraban" data-main="#address" id="error_address"><?= esc_html__('Debes informar tu dirección', 'woocommerce-mercadopago'); ?></span>
                            </div>

                            <div class="col-md-4" id="box-lastname">
                                <label for="number" class="mp-label-form"><?= esc_html__('Número', 'woocommerce-mercadopago'); ?> <em>*</em></label>
                                <input type="text" value="<?= $febraban['number']; ?>" id="number" class="mp-form-control" name="mercadopago_ticket[number]">
                                <span class="erro_febraban" data-main="#number" id="error_number"><?= esc_html__('Debe informar su número de dirección', 'woocommerce-mercadopago'); ?></span>
                            </div>
                        </div>

                        <div class="row pt-10">
                            <div class="col-md-4 pr-15">
                                <label for="city" class="mp-label-form"><?= esc_html__('Ciudad', 'woocommerce-mercadopago'); ?> <em>*</em></label>
                                <input type="text" value="<?= $febraban['city']; ?>" id="city" class="mp-form-control" name="mercadopago_ticket[city]">
                                <span class="erro_febraban" data-main="#city" id="error_city"><?= esc_html__('Debes informar a tu ciudad', 'woocommerce-mercadopago'); ?></span>
                            </div>

                            <div class="col-md-4 pr-15">
                                <label for="state" class="mp-label-form"><?= esc_html__('Estado', 'woocommerce-mercadopago'); ?> <em>*</em></label>
                                <select name="mercadopago_ticket[state]" id="state" class="mp-form-control pointer">
                                <option value="" <?php if ($febraban['state'] == '') { echo 'selected="selected"'; } ?>><?= esc_html__('Seleccione estado', 'woocommerce-mercadopago'); ?></option>
                                    <option value="AC" <?php if ($febraban['state'] == 'AC') { echo 'selected="selected"'; } ?>>Acre</option>
                                    <option value="AL" <?php if ($febraban['state'] == 'AL') { echo 'selected="selected"'; } ?>>Alagoas</option>
                                    <option value="AP" <?php if ($febraban['state'] == 'AP') { echo 'selected="selected"'; } ?>>Amapá</option>
                                    <option value="AM" <?php if ($febraban['state'] == 'AM') { echo 'selected="selected"'; } ?>>Amazonas</option>
                                    <option value="BA" <?php if ($febraban['state'] == 'BA') { echo 'selected="selected"'; } ?>>Bahia</option>
                                    <option value="CE" <?php if ($febraban['state'] == 'CE') { echo 'selected="selected"'; } ?>>Ceará</option>
                                    <option value="DF" <?php if ($febraban['state'] == 'DF') { echo 'selected="selected"'; } ?>>Distrito</option>
                                    <option value="ES" <?php if ($febraban['state'] == 'ES') { echo 'selected="selected"'; } ?>>Espírito</option>
                                    <option value="GO" <?php if ($febraban['state'] == 'GO') { echo 'selected="selected"'; } ?>>Goiás</option>
                                    <option value="MA" <?php if ($febraban['state'] == 'MA') { echo 'selected="selected"'; } ?>>Maranhão</option>
                                    <option value="MT" <?php if ($febraban['state'] == 'MT') { echo 'selected="selected"'; } ?>>Mato</option>
                                    <option value="MS" <?php if ($febraban['state'] == 'MS') { echo 'selected="selected"'; } ?>>Mato</option>
                                    <option value="MG" <?php if ($febraban['state'] == 'MG') { echo 'selected="selected"'; } ?>>Minas</option>
                                    <option value="PA" <?php if ($febraban['state'] == 'PA') { echo 'selected="selected"'; } ?>>Pará</option>
                                    <option value="PB" <?php if ($febraban['state'] == 'PB') { echo 'selected="selected"'; } ?>>Paraíba</option>
                                    <option value="PR" <?php if ($febraban['state'] == 'PR') { echo 'selected="selected"'; } ?>>Paraná</option>
                                    <option value="PE" <?php if ($febraban['state'] == 'PE') { echo 'selected="selected"'; } ?>>Pernambuco</option>
                                    <option value="PI" <?php if ($febraban['state'] == 'PI') { echo 'selected="selected"'; } ?>>Piauí</option>
                                    <option value="RJ" <?php if ($febraban['state'] == 'RJ') { echo 'selected="selected"'; } ?>>Rio de Janeiro</option>
                                    <option value="RN" <?php if ($febraban['state'] == 'RN') { echo 'selected="selected"'; } ?>>Rio Grande do Norte</option>
                                    <option value="RS" <?php if ($febraban['state'] == 'RS') { echo 'selected="selected"'; } ?>>Rio Grande do Sul</option>
                                    <option value="RO" <?php if ($febraban['state'] == 'RO') { echo 'selected="selected"'; } ?>>Rondônia</option>
                                    <option value="RA" <?php if ($febraban['state'] == 'RA') { echo 'selected="selected"'; } ?>>Roraima</option>
                                    <option value="SC" <?php if ($febraban['state'] == 'SC') { echo 'selected="selected"'; } ?>>Santa</option>
                                    <option value="SP" <?php if ($febraban['state'] == 'SP') { echo 'selected="selected"'; } ?>>São Paulo</option>
                                    <option value="SE" <?php if ($febraban['state'] == 'SE') { echo 'selected="selected"'; } ?>>Sergipe</option>
                                    <option value="TO" <?php if ($febraban['state'] == 'TO') { echo 'selected="selected"'; } ?>>Tocantins</option>
                                </select>
                                <span class="erro_febraban" data-main="#state" id="error_state"><?php echo esc_html__('Debes informar a tu estado', 'woocommerce-mercadopago'); ?></span>
                            </div>

                            <div class="col-md-4">
                                <label for="zipcode" class="mp-label-form"><?= esc_html__('Código postal', 'woocommerce-mercadopago'); ?> <em>*</em></label>
                                <input type="text" value="<?= $febraban['zipcode']; ?>" id="zipcode" class="mp-form-control" name="mercadopago_ticket[zipcode]">
                                <span class="erro_febraban" data-main="#zipcode" id="error_zipcode"><?= esc_html__('Debes informar tu código postal', 'woocommerce-mercadopago'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-12 pt-10 pb-30">
                            <div class="frame-tarjetas">
                                <div class="row pt-10">
                                    <p class="mp-obrigatory"><?= esc_html__('Completa todos los campos, son obligatorios.', 'woocommerce-mercadopago'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="frame-tarjetas">
                            <?php if (count($payment_methods) > 1) : ?>
                                <p class="subtitle-ticket-checkout"><?=__('Por favor, selecciona el emisor de su elección', 'woocommerce-mercadopago')?></p>

                                <div class="row pt-10">
                                    <?php $atFirst = true; ?>
                                    <?php foreach ($payment_methods as $payment) : ?>
                                        <div class="col-md-6 pb-15 min-hg">
                                            <div id="paymentMethodIdTicket" class="ticket-payments">
                                                <label for="<?= $payment['id']; ?>" class="mp-label-form pointer">
                                                    <input type="radio" class="mp-form-control-check" name="mercadopago_ticket[paymentMethodId]" id="<?= $payment['id'] ?>" value="<?= $payment['id']; ?>" <?php if ($atFirst) : ?> checked="checked" <?php endif; ?> />
                                                    <img src="<?= $payment['secure_thumbnail'] ?>" alt="<?php echo $payment['name']; ?>" />
                                                    <span class="ticket-name"><?= $payment['name'] ?></span>
                                                </label>
                                            </div>
                                            <?php $atFirst = false; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <input type="hidden" name="mercadopago_ticket[paymentMethodId]" id="<?= $payment['id']; ?>" value="<?= $payment['id']; ?>" />
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- NOT DELETE LOADING-->
        <div id="mp-box-loading"></div>

        <!-- utilities -->
        <div id="mercadopago-utilities">
            <input type="hidden" id="site_id" value="<?php echo $site_id; ?>" name="mercadopago_ticket[site_id]" />
            <input type="hidden" id="amountTicket" value="<?php echo $amount; ?>" name="mercadopago_ticket[amount]" />
            <input type="hidden" id="currency_ratioTicket" value="<?php echo $currency_ratio; ?>" name="mercadopago_ticket[currency_ratio]" />
            <input type="hidden" id="campaign_idTicket" name="mercadopago_ticket[campaign_id]" />
            <input type="hidden" id="campaignTicket" name="mercadopago_ticket[campaign]" />
            <input type="hidden" id="discountTicket" name="mercadopago_ticket[discount]" />
        </div>

    </div>
</div>

<script type="text/javascript" src="<?php echo $path_to_javascript; ?>"></script>
<script type="text/javascript">
    MPv1Ticket.text.apply = "<?php echo __('Aplicar', 'woocommerce-mercadopago'); ?>";
	MPv1Ticket.text.remove = "<?php echo __('Retirar', 'woocommerce-mercadopago'); ?>";
	MPv1Ticket.text.coupon_empty = "<?php echo __('Por favor, informe su código de cupón', 'woocommerce-mercadopago'); ?>";
	MPv1Ticket.text.choose = "<?php echo __('Escoger', 'woocommerce-mercadopago'); ?>";
	MPv1Ticket.text.other_bank = "<?php echo __('Otro banco', 'woocommerce-mercadopago'); ?>";
	MPv1Ticket.text.discount_info1 = "<?php echo __('Salvarás', 'woocommerce-mercadopago'); ?>";
	MPv1Ticket.text.discount_info2 = "<?php echo __('con descuento de', 'woocommerce-mercadopago'); ?>";
	MPv1Ticket.text.discount_info3 = "<?php echo __('Total de su compra:', 'woocommerce-mercadopago'); ?>";
	MPv1Ticket.text.discount_info4 = "<?php echo __('Total de su compra con descuento:', 'woocommerce-mercadopago'); ?>";
	MPv1Ticket.text.discount_info5 = "<?php echo __('*Tras la aprobación del pago', 'woocommerce-mercadopago'); ?>";
	MPv1Ticket.text.discount_info6 = "<?php echo __('Términos y condiciones de uso', 'woocommerce-mercadopago'); ?>";
    MPv1Ticket.paths.loading = "<?php echo ($images_path . 'loading.gif'); ?>";
    MPv1Ticket.paths.check = "<?php echo ($images_path . 'check.png'); ?>";
    MPv1Ticket.paths.error = "<?php echo ($images_path . 'error.png'); ?>";

    MPv1Ticket.Initialize(
        "<?php echo $site_id; ?>",
        "<?php echo $coupon_mode; ?>" == "yes",
        "<?php echo $discount_action_url; ?>",
        "<?php echo $payer_email; ?>"
    );
</script>