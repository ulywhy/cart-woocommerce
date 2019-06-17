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

<div class="panel-checkout">
  <div class="row">
    <h2 class="title-checkout">Usa el medio de pago que prefieras.</h2>
    
    <?php if($credito != 0): ?>
    <div class="col-md-12">
        <div class="frame-tarjetas">
            <p class="subtitle-checkout">
                Tarjetas de crédito
                <span class="badge-checkout">Hasta <?= $installments ?> cuotas</span>
            </p>
            
            <?php foreach($tarjetas as $tarjeta): ?>
              <?php if ($tarjeta['type'] == 'credit_card'): ?>
                <img src="<?= $tarjeta['image'] ?>" class="img-fluid img-tarjetas" />
              <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if($debito != 0): ?>
    <div class="col-md-6">
        <div class="frame-tarjetas">
            <p class="subtitle-checkout">Tarjetas de débito</p>
            
            <?php foreach($tarjetas as $tarjeta): ?>
              <?php if ($tarjeta['type'] == 'debit_card' || $tarjeta['type'] == 'prepaid_card'): ?>
                <img src="<?= $tarjeta['image'] ?>" class="img-fluid img-tarjetas" />
              <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if($efectivo != 0): ?>
    <div class="col-md-6">
        <div class="frame-tarjetas">
            <p class="subtitle-checkout">Pagos en efectivo</p>
            
            <?php foreach($tarjetas as $tarjeta): ?>
              <?php if ($tarjeta['type'] != 'credit_card' && $tarjeta['type'] != 'debit_card' && $tarjeta['type'] != 'prepaid_card'): ?>
                <img src="<?= $tarjeta['image'] ?>" class="img-fluid img-tarjetas" />
              <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="col-md-12 pt-20">
        <div class="redirect-frame">
            <img src="<?= $cho_image ?>" class="img-fluid" />
            <p>Te llevamos a nuestro sitio para completar el pago</p>
        </div>
    </div>
    
  </div>
</div>