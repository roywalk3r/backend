$(document).ready(function() {
    // Animate FAQ open/close
    $('.faq-list details').each(function() {
        var details = $(this);
        var summary = details.find('summary');
        var answer = details.children('.faq-answer');
        answer.hide();
        details.removeAttr('open');
        summary.on('click', function(e) {
            e.preventDefault();
            if(details.attr('open')) {
                answer.slideUp(220);
                setTimeout(function() { details.removeAttr('open'); }, 220);
            } else {
                $('.faq-list details[open]').each(function() {
                    var openDetails = $(this);
                    openDetails.children('.faq-answer').slideUp(220);
                    setTimeout(function() { openDetails.removeAttr('open'); }, 220);
                });
                details.attr('open', '');
                answer.slideDown(220);
            }
        });
    });
});
