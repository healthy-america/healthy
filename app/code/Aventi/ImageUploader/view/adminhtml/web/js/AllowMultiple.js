require(["jquery","uiRegistry"], function($,uiRegistry) {
    $(function () {
        checkContainer();
        function checkContainer() {
            if($('.file-uploader.image-uploader').is(':visible')){ //if the container is visible on the page
                uiRegistry.get('images_form.images_form.image.image').isMultipleFiles = true;
                $('.file-uploader-button').hide()
            } else {
                setTimeout(checkContainer, 100); //wait 100 ms, then try again
            }
        }
    })
})
