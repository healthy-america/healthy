require(['jquery'], function($) {
    $(document).ready(function() {
        setTimeout(() => {
            const fields = {
                nickname: document.getElementById('nickname_field'),
                summary: document.getElementById('summary_field'),
                review: document.getElementById('review_field')
            };

            fields.nickname.value = ".";
            fields.summary.value = ".";
            fields.review.value = ".";

            Object.values(fields).forEach(field => $(field).trigger('input'));
        }, 4000);
    });
});
