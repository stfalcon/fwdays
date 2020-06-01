    var in_progress = false;

    function loadSrc() {
        if (in_progress) {return;}
        in_progress = true;

        $('.lazyload').each(function() {
            var delta_height = 0;
            if ($(this).data('img-height')) {
                delta_height = $(this).data('img-height');
            }

            if (window.scrollY + window.innerHeight > $(this).clientRect().top - delta_height) {
                if ($(this).data('src')) {
                    if ($(this).data('src') === 'script') {
                        var scripts = $(this).find('script');
                        scripts.each(function () {
                            if ($(this).data('time')) {
                                var script_elem = $(this);
                                setTimeout(function () {script_elem.attr('src', script_elem.data('src'));}, script_elem.data('time'));
                            } else {
                                $(this).attr('src', $(this).data('src'));
                            }
                        });
                    } else if ($(this).hasClass('lazyload-style')) {
                        $(this).css("background-image", "url('"+$(this).data('src')+"')");
                    } else {
                        $(this).attr('src', $(this).data('src'));
                    }
                }

                $(this).removeClass('lazyload');
            }
        })
        in_progress = false;
    }

$(function(){
    $(window).scroll(function () {
        loadSrc()
    });
});

loadSrc();
