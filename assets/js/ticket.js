(function ($) {
    'use strict';

    $(function () {
        var mercado_pago_submit = false;
        var mercado_pago_docnumber = "CPF";

        var seller = {
            site_id: wc_mercadopago_ticket_params.site_id,
        }

        // Load woocommerce checkout form
        $('body').on('updated_checkout', function () {
            if (seller.site_id == "MLB") {
                validateDocumentInputs();
            }
        });

        /**
         * Validate input depending on document type
         */
        function validateDocumentInputs() {
            var mp_box_lastname = document.getElementById("mp_box_lastname");
            var mp_box_firstname = document.getElementById("mp_box_firstname");
            var mp_firstname_label = document.getElementById("mp_firstname_label");
            var mp_socialname_label = document.getElementById("mp_socialname_label");
            var mp_cpf_label = document.getElementById("mp_cpf_label");
            var mp_cnpj_label = document.getElementById("mp_cnpj_label");
            var mp_doc_number = document.getElementById("mp_doc_number");

            $('input[type=radio][name="mercadopago_ticket[docType]"]').change(function () {
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
                    mercado_pago_docnumber = "CPF";
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
                    mercado_pago_docnumber = "CNPJ";
                }
            });
        }

        /**
         * Handler form submit
         * @return {bool}
         */
        function mercadoPagoFormHandler() {
            if (seller.site_id == "MLB") {
                if (validateInputs() && validateDocumentNumber()) {
                    mercado_pago_submit = true;
                }

                return mercado_pago_submit;
            }
        }

        // Process when submit the checkout form.
        $('form.checkout').on('checkout_place_order_woo-mercado-pago-ticket', function () {
            return mercadoPagoFormHandler();
        });

        // If payment fail, retry on next checkout page
        $('form#order_review').submit(function () {
            return mercadoPagoFormHandler();
        });

        /**
         * Get form
         */
        function getForm() {
            return document.querySelector('#mercadopago-form-ticket');
        }

        /**
         * Validate if all inputs are valid
         */
        function validateInputs() {
            var form = getForm();
            var form_inputs = form.querySelectorAll("[data-checkout]");
            var span = form.querySelectorAll(".mp-erro_febraban");

            //Show or hide error message and border
            for (var i = 0; i < form_inputs.length; i++) {
                var element = form_inputs[i];
                var input = form.querySelector(span[i].getAttribute("data-main"));

                if (element.parentNode.style.display != "none" && (element.value == -1 || element.value == "")) {
                    span[i].style.display = "inline-block";
                    input.classList.add("mp-form-control-error");
                } else {
                    span[i].style.display = "none";
                    input.classList.remove("mp-form-control-error");
                }
            }

            //Focus on the element with error
            for (var i = 0; i < form_inputs.length; i++) {
                var element = form_inputs[i];
                if (element.parentNode.style.display != "none" && (element.value == -1 || element.value == "")) {
                    element.focus();
                    return false;
                }
            }

            return true;
        }

        /**
         * Validate document number
         * @return {bool}
         */
        function validateDocumentNumber() {
            var docnumber_input = document.getElementById("mp_doc_number");
            var docnumber_error = document.getElementById("mp_error_docnumber");
            var docnumber_validate = false;

            if (mercado_pago_docnumber == "CPF") {
                docnumber_validate = validateCPF(document.getElementById("mp_doc_number").value);
            } else {
                docnumber_validate = validateCNPJ(document.getElementById("mp_doc_number").value);
            }

            if (!docnumber_validate) {
                docnumber_error.style.display = "block";
                docnumber_input.classList.add("mp-form-control-error");
                docnumber_input.focus();
            } else {
                docnumber_error.style.display = "none";
                docnumber_input.classList.remove("mp-form-control-error");
                docnumber_validate = true;
            }

            return docnumber_validate;
        }

        /**
         * Validate CPF
         * @param {string} strCPF
         * @return {bool}
         */
        function validateCPF(strCPF) {
            var Soma;
            var Resto;

            Soma = 0;
            strCPF = strCPF.replace(/[.-\s]/g, "");

            if (strCPF == "00000000000") {
                return false;
            }

            for (var i = 1; i <= 9; i++) {
                Soma = Soma + parseInt(strCPF.substring(i - 1, i)) * (11 - i);
            }

            Resto = (Soma * 10) % 11;
            if ((Resto == 10) || (Resto == 11)) { Resto = 0; }
            if (Resto != parseInt(strCPF.substring(9, 10))) {
                return false;
            }

            Soma = 0;
            for (var i = 1; i <= 10; i++) { Soma = Soma + parseInt(strCPF.substring(i - 1, i)) * (12 - i); }

            Resto = (Soma * 10) % 11;
            if ((Resto == 10) || (Resto == 11)) { Resto = 0; }
            if (Resto != parseInt(strCPF.substring(10, 11))) {
                return false;
            }

            return true;
        }

        /**
         * Validate CNPJ
         * @param {string} strCNPJ
         * @return {bool}
         */
        function validateCNPJ(strCNPJ) {
            var numeros, digitos, soma, i, resultado, pos, tamanho, digitos_iguais;

            strCNPJ = strCNPJ.replace(".", "");
            strCNPJ = strCNPJ.replace(".", "");
            strCNPJ = strCNPJ.replace(".", "");
            strCNPJ = strCNPJ.replace("-", "");
            strCNPJ = strCNPJ.replace("/", "");
            digitos_iguais = 1;

            if (strCNPJ.length < 14 && strCNPJ.length < 15) {
                return false;
            }
            for (i = 0; i < strCNPJ.length - 1; i++) {
                if (strCNPJ.charAt(i) != strCNPJ.charAt(i + 1)) {
                    digitos_iguais = 0;
                    break;
                }
            }
            if (!digitos_iguais) {
                tamanho = strCNPJ.length - 2
                numeros = strCNPJ.substring(0, tamanho);
                digitos = strCNPJ.substring(tamanho);
                soma = 0;
                pos = tamanho - 7;

                for (i = tamanho; i >= 1; i--) {
                    soma += numeros.charAt(tamanho - i) * pos--;
                    if (pos < 2) {
                        pos = 9;
                    }
                }

                resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
                if (resultado != digitos.charAt(0)) {
                    return false;
                }

                tamanho = tamanho + 1;
                numeros = strCNPJ.substring(0, tamanho);
                soma = 0;
                pos = tamanho - 7;
                for (i = tamanho; i >= 1; i--) {
                    soma += numeros.charAt(tamanho - i) * pos--;
                    if (pos < 2) {
                        pos = 9;
                    }
                }

                resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
                if (resultado != digitos.charAt(1)) {
                    return false;
                }

                return true;
            }
            else {
                return false;
            }
        }
    });

}(jQuery));