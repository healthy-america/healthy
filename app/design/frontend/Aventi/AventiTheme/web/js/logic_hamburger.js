const ubicationDom = 'header.page-header .middle-header .minicart-wrapper'


// Hamburger Click
const hamburgerEvent = () => {

    try {
        //Extrae el boton del sitio
        const miniCarrito = document.querySelector(`${ubicationDom} a.action.showcart`);
        const closeMiniCart = document.querySelector(`${ubicationDom} div#minicart-content-wrapper #close-minicart`);
        const auxModalClose = document.querySelector('.close-modal-mini-cart');
        const fatherBox = document.querySelector(ubicationDom);

        if (!miniCarrito && !closeMiniCart) return;

        //Agrega el evento click
        miniCarrito.addEventListener('click', (e) => {
            //cada vez que se da click, cierra el otro menu, para evitar que se abran los dos
            const _a = document.querySelector('html');
            _a.classList.remove('nav-before-open','nav-open');
            miniCarrito.classList.add('hidden-element');
            auxModalClose.classList.remove('hidden-element');
            //remove after ubicationDom
            
            if (!fatherBox.classList.contains('remove-after')) {
                fatherBox.classList.add('remove-after');
            }

        });

        //Agrega el evento click
        closeMiniCart.addEventListener('click', (e) => {
            miniCarrito.classList.remove('hidden-element');
            auxModalClose.classList.add('hidden-element');
        });

        //click de la parte del background
        auxModalClose.addEventListener('click', function(event) {
            miniCarrito.classList.remove('hidden-element');
            fatherBox.classList.remove('active');
            this.classList.add('hidden-element');
        });


        
    } catch (error) {
        console.log(error);   
    }

}

// Inicializa el evento
document.addEventListener('DOMContentLoaded', () => {
    hamburgerEvent();
});