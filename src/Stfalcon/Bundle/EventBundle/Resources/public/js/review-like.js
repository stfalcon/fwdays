$(document).ready(function (){
    var $reviewLikeLink = $('.button-like a.voter');


    $reviewLikeLink.on('click', function(e) {
        e.preventDefault();
        var $reviewLikeCnt = $(this).parent();
        var $reviewLikeCounter = $reviewLikeCnt.find('.button-like-top');
        $.ajax({
            'url': $(this).attr('href'),
            async: false,
            success: function (response) {
                var likesCount = response.likesCount;

                if (undefined === likesCount) {
                    window.location.href = '/login';
                }

                $reviewLikeCnt.toggleClass('active');
                if (likesCount > 0) {
                    if ($reviewLikeCounter.hasClass('empty')) {
                        $reviewLikeCounter.removeClass('empty');
                    }
                    $reviewLikeCounter.text(likesCount);
                } else {
                    if (!$reviewLikeCounter.hasClass('empty')) {
                        $reviewLikeCounter.addClass('empty');
                    }
                    $reviewLikeCounter.text('');
                }
            }
        });
    });
});