<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="panel-checkout">
  <div class="row">
    <?php if($credito != 0): ?>
    <div id="framePayments" class="col-md-12">
        <div class="frame-tarjetas">
            <p class="subtitle-checkout">
                <?= __('Tarjetas de crédito', 'woocommerce-mercadopago') ?>
                <span class="badge-checkout"><?=__('Hasta', 'woocommerce-mercadopago')?> <?= $installments ?> <?=__($str_cuotas, 'woocommerce-mercadopago')?></span>
            </p>
            
            <?php foreach($tarjetas as $tarjeta): ?>
              <?php if ($tarjeta['type'] == 'credit_card'): ?>
                <img src="<?= $tarjeta['image'] ?>" class="img-fluid img-tarjetas" alt=""/>
              <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if($debito != 0): ?>
    <div id="framePayments" class="col-md-6 pr-15">
        <div class="frame-tarjetas">
            <p class="subtitle-checkout"><?=__('Tarjetas de débito', 'woocommerce-mercadopago')?></p>
            
            <?php foreach($tarjetas as $tarjeta): ?>
              <?php if ($tarjeta['type'] == 'debit_card' || $tarjeta['type'] == 'prepaid_card'): ?>
                <img src="<?= $tarjeta['image'] ?>" class="img-fluid img-tarjetas" alt="" />
              <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if($efectivo != 0): ?>
    <div id="framePayments" class="col-md-6">
        <div class="frame-tarjetas">
            <p class="subtitle-checkout"><?=__('Pagos en efectivo', 'woocommerce-mercadopago')?></p>
            
            <?php foreach($tarjetas as $tarjeta): ?>
              <?php if ($tarjeta['type'] != 'credit_card' && $tarjeta['type'] != 'debit_card' && $tarjeta['type'] != 'prepaid_card'): ?>
                <img src="<?= $tarjeta['image'] ?>" class="img-fluid img-tarjetas" alt=""/>
              <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="col-md-12 pt-20">
        <div class="redirect-frame">
            <img src="<?= $cho_image ?>" class="img-fluid" alt=""/>
            <p><?=__('Te llevamos a nuestro sitio para completar el pago', 'woocommerce-mercadopago')?></p>
        </div>
    </div>
    
  </div>
</div>

<script type="text/javascript" src="<?php echo $path_to_javascript; ?>"></script>