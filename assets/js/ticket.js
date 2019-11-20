(function ($) {
    'use strict';

    $(function () {
        var mercado_pago_submit = false;

        var seller = {
            site_id: wc_mercadopago_ticket_params.site_id,
        }

        $('body').on('updated_checkout', function () {
            validateDocumentInputs();
        });

        /**
         * Validate input depending on document type
         */
        function validateDocumentInputs(){
            var mp_box_lastname = document.getElementById("mp_box_lastname");
            var mp_box_firstname = document.getElementById("mp_box_firstname");
            var mp_firstname_label = document.getElementById("mp_firstname_label");
            var mp_socialname_label = document.getElementById("mp_socialname_label");

            var mp_cpf_label = document.getElementById("mp_cpf_label");
            var mp_cnpj_label = document.getElementById("mp_cnpj_label");
            var mp_doc_number = document.getElementById("mp_doc_number");

            $('input[type=radio][name="mercadopago_ticket[docType]"]').change(function() {
                if (this.value == 'CPF') {
                    mp_cpf_label.style.display = "block";
                    mp_box_lastname.style.display = "block";
                    mp_firstname_label.style.display = "block";
                    mp_cnpj_label.style.display = "none";
                    mp_socialname_label.style.display = "none";
                    mp_box_firstname.classList.add("mp-col-md-4");
                    mp_box_firstname.classList.remove("mp-col-md-8");
                    mp_doc_number.setAttribute("maxlength", "14");
                    mp_doc_number.setAttribute("onkeyup", "maskinput(this, mcpf)");
                } else {
                    mp_cpf_label.style.display = "none";
                    mp_box_lastname.style.display = "none";
                    mp_firstname_label.style.display = "none";
                    mp_cnpj_label.style.display = "block";
                    mp_socialname_label.style.display = "block";
                    mp_box_firstname.classList.add("mp-col-md-8");
                    mp_box_firstname.classList.remove("mp-col-md-4");
                    mp_doc_number.setAttribute("maxlength", "18");
                    mp_doc_number.setAttribute("onkeyup", "maskinput(this, mcnpj)");
                }
            });
        }
    });

}(jQuery));