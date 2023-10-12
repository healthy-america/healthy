require([
    'jquery',
    'mage/cookies'
], function ($) {

    const prehome = $('main#prehome');
    const check = $.cookie('prehomecookie');
    const body = $('body');
    const loader = $('div.loader');
    const homebody = $('.page-wrapper');

    prehome.ready(function() {
        body.css('overflow', 'hidden');
        homebody.css('display','none');
        if (!prehome.hasClass('display-none_')) {
            prehome.addClass('display-none_');
        }

        if (check) {
            body.css('overflow', 'auto')
            homebody.css('display','flex')
            prehome.fadeOut()
        }else{
            prehome.removeClass('display-none_')
            loader.fadeOut()
        }

        prehome.find('img').on('click', function () {
            let url = BASE_URL.replace('index.php/', '') + 'prehome/index/setcookie';
            $.ajax({
                url: url,
                type: "POST",
                data: {
                    name: 'prehomecookie',
                    value: 1
                },
                cache: false,
                success: function (data) {
                    $('body').css('overflow', 'auto');
                    homebody.css('display','flex');
                    prehome.fadeOut();
                }
            });
        })
    });
});
