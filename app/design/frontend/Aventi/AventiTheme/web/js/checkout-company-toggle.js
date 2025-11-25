
require(['jquery'], function($) {
  $(document).ready(function() {
    // Esperar a que Knockout cargue los elementos del checkout
    const observer = new MutationObserver(function() {
      const $tipoPersona = $('select[name="suffix"]');
      const $tipoPersonaContent = $('[name="shippingAddress.suffix"]');
      const $campoEmpresa = $('[name="shippingAddress.company"]');

      if ($tipoPersona.length && $campoEmpresa.length) {
        // Ocultar por defecto
        $campoEmpresa.hide();

        // Controlar el cambio de tipo de persona
        $tipoPersona.on('change', function() {
          const valor = $(this).val();

          if (valor === 'Legal') { // Jurídica
            // Validar resolución de pantalla
            if ($(window).width() > 768) {
                $tipoPersonaContent.css({
                    'width': '50%'
                });
            } else {
                $tipoPersonaContent.css({
                    'width': '100%'
                });
            }
            $campoEmpresa
              .show();
          } else {
            $campoEmpresa.hide();
            $tipoPersonaContent
              .css({
                'width': '100%'
              });
          }
        });

        // Una vez encontrado, deja de observar
        observer.disconnect();
      }
    });

    // Observar los cambios del DOM del checkout
    observer.observe(document.body, { childList: true, subtree: true });
  });
});
