    var in_progress = false;

    function loadSrc() {
        if (in_progress) {return;}
        in_progress = true;

        $('.lazyload').each(function() {
            if (window.scrollY + window.innerHeight > $(this).clientRect().top) {
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
    loadSrc();

    $(window).scroll(function () {
        loadSrc()
    });
});