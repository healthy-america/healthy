require([
    'jquery',
    'mage/cookies'
], function ($) {

    $(document).ready(function () {
        const prehome = $('main#prehome'),
              body = $('body'),
              loader = $('div.loader'),
              homebody = $('.page-wrapper');
    
        $('#delete-cookie-home').on('click', async function () {
            const url = BASE_URL.replace('index.php/', '') + 'prehome/index/setcookie';
            $.ajax({
                url: url,
                type: "POST",
                data: {
                    name: 'prehomecookie',
                    value: null
                },
                cache: false,
                success: function (data) {
                    console.log(prehome, prehome.length === 0);
                    //saber si existe el prehome

                    if (prehome.length === 0) {
                        //ir a la raiz;
                        window.location.href = BASE_URL.replace('index.php/', '');
                    }
                    else {
                        body.css('overflow', 'hidden');
                        homebody.css('display','none');
                        prehome.removeClass('display-none_')
                        loader.fadeOut()
                    }
                }
            });
        });
    });
});