$(document).ready(function () {
    /**
     * Show password
     */
    $('.icon-password--js').on('click', function () {
        var input = $(this).siblings('input'),
            type = input.attr('type');

        $(this).toggleClass("input-password__icon--active");

        if (type === "password") {
            input.attr("type", "text");
        } else {
            input.attr("type", "password");
        }
    });

    /**
     * Mask phone input
     */
    $('.mask-phone-input--js').mask("+38 099 999 99 99",
        {
            placeholder: " ",
            autoclear: false
        });

    /**
     * Payment popup
     */
    $('#add-user-trigger').on('click', function (e) {
        e.preventDefault();
        $(this).hide();
        $('#payment-add-user').show();
    });

    $('#cancel-add-user').on('click', function (e) {
        e.preventDefault();
        $('#payment-add-user').hide();
        $('#add-user-trigger').show();
    });

    $('#promo-code-trigger').on('click', function (e) {
        e.preventDefault();
        $(this).hide();
        $('#add-promo-code').show();
    });

    $('#cancel-promo-code').on('click', function (e) {
        e.preventDefault();
        $('#add-promo-code').hide();
        $('#promo-code-trigger').show();
    });

    /**
     * Program navigation for mobile devices
     */
    $(window).bind('resize load', function () {
        if ($(window).width() < 768) {
            $('.program-header__td').on('click', function () {
                var currentIndex = $(this).index() + 2,
                    eventRow = $('.program-body__tr--event');

                $(this).addClass('program-header__td--active')
                    .siblings()
                    .removeClass('program-header__td--active');
                eventRow.find('.program-body__td').not('.program-body__td:nth-child(1)').hide();
                eventRow.find('.program-body__td:nth-child(' + currentIndex + ')').show();
            });
        }
    });

    /**
     * Dropdown for referral link
     */
    $('#ref-dropdown').on('change', function () {
        var value = $('option:selected', this).text(),
            ref = $('option:selected', this).data('ref');

        $('#ref-selected').text(value);
        $('#ref-input').val(ref);
    });

    /**
     * Referral link copy yo clipboard
     */
    var isiOs = navigator.userAgent.match(/ipad|ipod|iphone/i);
    
    $('#ref-copy').on('click', function () {
        var input = $('#ref-input');

        if (isiOs) {
            var el = input.get(0),
                editable = el.contentEditable,
                readOnly = el.readOnly;
            el.contentEditable = true;
            el.readOnly = false;

            var range = document.createRange();
            range.selectNodeContents(el);

            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
            el.setSelectionRange(0, 999999);
            el.contentEditable = editable;
            el.readOnly = readOnly;
        } else {
            input.select();
        }

        var clipBoard = document.execCommand('copy');
        if (clipBoard) {
            $(this).addClass('tooltip-copy--active');
        }

        var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        if (isMobile) {
            input.blur();

            if ($(this).hasClass('tooltip-copy--active')) {
                setTimeout(function () {
                    $('.tooltip-copy').removeClass('tooltip-copy--active');
                }, 2000);
            }
        }
    });

    $('.tooltip-copy').on('mouseout', function () {
        if ($(this).hasClass('tooltip-copy--active')) {
            setTimeout(function () {
                $('.tooltip-copy').removeClass('tooltip-copy--active');
            }, 1000);
        }
    });

    /**
     * Show the terms of the program for mobile devices
     */
    $('.share-banner__hint-show').on('click', function () {
        $(this).hide();
        $('.share-banner__hint').show();
    });


    /**
     * Fixed event header on scroll for event-page
     */
    if ($('.event-header').length) {
        var eventHeader = $('.event-header'),
            eventHeaderFixed = $('.fix-event-header');

        $(document).bind('scroll load', function () {
            var eventHeaderHeight = eventHeader.outerHeight(),
                offsetTopEvent = eventHeader.offset().top;

            if ($(this).scrollTop() > eventHeaderHeight + offsetTopEvent) {
                eventHeaderFixed.addClass('fix-event-header--show');
            } else {
                eventHeaderFixed.removeClass('fix-event-header--show');
            }
        });
    }

    /**
     * Fixed event static header on scroll for review-page, venue-page
     */
    var eventHeaderFixedStat = $('.fix-event-header--static'),
        sectionAfterEventHeader = $('.section-after-event-header');

    $(document).bind('scroll load', function () {
        var headerHeight = $('.header').outerHeight();

        if ($(this).scrollTop() >= headerHeight) {
            sectionAfterEventHeader.addClass('section-after-event-header--mr-t');
            eventHeaderFixedStat.addClass('fix-event-header--fixed');
        } else {
            sectionAfterEventHeader.removeClass('section-after-event-header--mr-t');
            eventHeaderFixedStat.removeClass('fix-event-header--fixed');
        }
    });

    /**
     * Fixed program header on scroll
     */
    if ($('.program').length) {
        var program = $('.program'),
            programBody = $('.program-body'),
            programHeader = $('.program-header'),
            offsetTopNegative;

        if ($(window).width() >= 768) {
            offsetTopNegative = 28;
        } else {
            offsetTopNegative = 0;
        }

        $(document).bind('scroll load', function () {
            var programHeight = program.outerHeight(),
                programHeaderHeight = programHeader.outerHeight(),
                headerFixedHeight = $('.fix-event-header').outerHeight(),
                offsetTopProgram = program.offset().top - headerFixedHeight;

            if ($(this).scrollTop() > offsetTopProgram + offsetTopNegative && $(this).scrollTop() < offsetTopProgram + offsetTopNegative + programHeight - programHeaderHeight) {
                programHeader.addClass('program-header--fixed');
                programBody.addClass('program-body--header-fixed');
                programHeader.removeClass('program-header--absolute');
            } else if ($(this).scrollTop() > offsetTopProgram + programHeight - programHeaderHeight) {
                programHeader.addClass('program-header--absolute');
                programHeader.removeClass('program-header--fixed');
            } else {
                programHeader.removeClass('program-header--fixed');
                programHeader.removeClass('program-header--absolute');
                programBody.removeClass('program-body--header-fixed');
            }
        });
    }

    /**
     * Show event menu for mobile devices
     */
    var eventMenuTrigger = $('#event-menu-trigger'),
        eventMenu = $('.event-menu');

    eventMenuTrigger.on('click', function () {
        $(this).toggleClass('open');
        $('body').toggleClass('overlay');
        eventMenu.toggleClass('event-menu--open');
    });

    if (eventMenuTrigger.length) {
        $(document).bind('click touchstart', function (e) {
            var el = $(e.target);

            if (el.closest('#event-menu-trigger').length || el.closest('.event-menu').length) {
                return true;
            } else {
                closeMenu();
            }
        });
    }

    /**
     * Go to block
     */
    var goToBlock = $(".go-to-block");

    goToBlock.click(function (e) {
        e.preventDefault();
        animateScroll($(this).attr('href'));

        if (eventMenu.length) {
            closeMenu();
        }
    });

    /**
     * Anchor from another page
     */
    $(window).bind("load", function () {
        if (window.location.hash) {
            setTimeout(function () {
                animateScroll(window.location.hash);
            }, 0);
        }
    });

    function animateScroll(target) {
        var fixHeader = $('.fix-event-header'),
            sumOffset;

        if (fixHeader.length) {
            sumOffset = $('.fix-event-header').outerHeight() + 24;
        } else {
            sumOffset = 0;
        }

        $('html, body').animate({
            scrollTop: ($(target).offset().top - sumOffset)
        }, 500);
    }

    function closeMenu() {
        $('body').removeClass('overlay');
        eventMenuTrigger.removeClass('open');
        eventMenu.removeClass('event-menu--open');
    }

    /**
     *  Button for scroll top page
     */
    if ($('.btn-up').length && $(window).width() > 1024) {
        $(window).scroll(function () {
            var headerHeight = $('.header').outerHeight(),
                eventHeaderHeight = $('.event-header').outerHeight(),
                windowHeight = $(window).outerHeight(),
                footerOffsetTop = $('.footer').offset().top,
                scrollHeight = $(this).scrollTop(),
                mapOffsetTop = $('.location__map').length ? $('.location__map').offset().top : 0,
                mapMrTop = parseInt($('.location__map').length ? $('.location__map').css('margin-top') : 0),
                footerMrTop = parseInt($('.footer').css('margin-top')),
                sumHeight = scrollHeight + windowHeight;

            if (scrollHeight > headerHeight + eventHeaderHeight) {
                $('.btn-up').addClass('btn-up--visible');
            } else {
                $('.btn-up').removeClass('btn-up--visible');
            }

            if ($('.location__map').length && sumHeight - 87 > mapOffsetTop - mapMrTop) {
                $('.btn-up').css('bottom', sumHeight - (mapOffsetTop - mapMrTop));
            }
            else if (sumHeight - 87 > footerOffsetTop - footerMrTop) {
                $('.btn-up').css('bottom', sumHeight - (footerOffsetTop - footerMrTop));
            }
            else {
                $('.btn-up').css('bottom', 87);
            }

        });

        $('.btn-up').on('click', function () {
            $('body,html').animate({
                scrollTop: 0
            }, 600);
            return false;
        });
    }
});