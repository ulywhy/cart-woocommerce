(function () {
    if (document.getElementById("payment") != undefined && document.getElementById("payment").offsetWidth <= 440) {
        var framePayments = document.querySelectorAll('#framePayments');
        for (var i = 0; i < framePayments.length; i++) {
            framePayments[i].className = '';
            framePayments[i].classList.add('col-md-12');
        }
    }
}).call();