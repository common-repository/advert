(function ($) {

    var viewed = [];

    $(document).ready(function () {

        curWindow = window.location.href;

        if ($('.advert-zone-wrapper').length > 0) {

            $curZoneStamp = $('.advert-zone').eq(0).data('tstamp');
            $curStamp = Math.floor(new Date().getTime() / 1000);
            if ($curStamp >= $curZoneStamp && (($curStamp - $curZoneStamp) > 30)) {

                $('.advert-zone').hide();
                var howmany = 0;
             
                $('.advert-zone-wrapper').each(function () {
                    var azw = '#' + $(this).attr('id');
                    var pazq = $(this).attr('id');
                    var curID = '#' + $(azw + ' .advert-zone').attr('id');
                    $(azw + ' .advert-zone').addClass('advert-changing-removal');

                    var data = {
                        action: 'advert_change_ads',
                        data: $(azw + ' .advert-wrapper').data('ads'),
                        count: howmany
                    };

                    $.post(avajaxurl, data, function (response) {
                        $(azw).prepend('<div id="advertplaceholderchanger-'+pazq+'"></div>');
                        $(azw + ' .advert-changing-removal').remove();
                        $('#advertplaceholderchanger-'+pazq).after(response);
                        $('#advertplaceholderchanger-'+pazq).remove();
                        if ($(curID + ' video').length > 0) {
                            setTimeout(function () {
                                $(azw).height($(curID + ' .advert-display-notice').outerHeight());
                            }, 500);
                        }
                        else { $(azw).height($(curID).height()); }
                        response = '';
                    });

                    howmany = howmany + 1;

                });

                setTimeout(function () {
                    $('.advert-zone-wrapper').each(function () {
                        $(this).removeAttr('style');
                    });
                }, 4000);

            }
        }

        //check if advert video and browser supports
        var advertVideo = (typeof (document.createElement('video').canPlayType) != 'undefined');
        if (advertVideo === false) {
            $('.advert-nvsupport').show();
        }



        //iframe clicking
        $(window).focus();
        $(window).blur(function (e) {
            var focusedElement = document.activeElement;
            var focusedElementClass = focusedElement.parentNode.className;
            focusedElement = focusedElement.parentNode.id;
            if (focusedElementClass === 'advert-wrapper' && e.originalEvent !== undefined && $('#avbded581').length === 0) {
                var data = {
                    action: 'advert_custom_log',
                    nonce: $('#' + focusedElement).data('nonce'),
                    data: $('#' + focusedElement).data('ads')
                };
                $.post(avajaxurl, data, function (response) { });
            }
        });

        //feedback
        $(document).on('click', '.advert-zone .advert-display-feedback li a', function (e) {
            e.preventDefault();

            var currentFeedback;
            currentFeedback = $(this).parent().parent().parent().attr('class');

            if ($(this).data('feedback') > 0 && e.originalEvent !== undefined && $('#avbded581').length === 0) {
                var data = {
                    action: 'advert_feedback_log',
                    nonce: $(this).parent().parent().data('nonce'),
                    data: $(this).parent().parent().data('ads'),
                    fbvalue: $(this).data('feedback')
                };
                $.post(avajaxurl, data, function (response) {
                    if (response == 'feedback') {
                        $('.' + currentFeedback + ' ul').remove();
                        $('.' + currentFeedback + ' .advert-feeback-return1').fadeIn();
                    }
                    else if (response.indexOf('Error:') >= 0) {
                        response = response.replace('Error:', '');
                        $('.' + currentFeedback + ' ul').remove();
                        $('.' + currentFeedback + ' .advert-feeback-return2').text(response).fadeIn();
                    }
                    else {
                        $('.' + currentFeedback + ' ul').remove();
                        $('.' + currentFeedback + ' .advert-feeback-return2').fadeIn();
                    }
                });

            }
            else { return; }
        });

        //advert feedback
        var adverthidefeedback1;
        var adverthidefeedback2;
        var currentFeedbackDisplay;
        var currentFeedbackZone;

        $(document).on('click', '.advert-zone .advert-choice', function () {
            if ($(this).next('.advert-display-feedback').is(':hidden')) {
                currentFeedbackDisplay = $(this).next('.advert-display-feedback').attr('id');
                $(this).next('.advert-display-feedback').show();
                setTimeout(function () {
                    $('#' + currentFeedbackDisplay).addClass('advert-feedback-open');
                }, 200);
            }
        });

        $(document).on('mouseenter', '.advert-zone', function () {
            if (currentFeedbackZone === this.id) {
                clearTimeout(adverthidefeedback1);
                clearTimeout(adverthidefeedback2);
            }
        });

        $(document).on('mouseleave', '.advert-zone', function () {
            currentFeedbackZone = this.id;
            adverthidefeedback1 = setTimeout(function () {
                $('#' + currentFeedbackZone + ' .advert-display-feedback').removeClass('advert-feedback-open');
            }, 1000);
            adverthidefeedback2 = setTimeout(function () {
                $('#' + currentFeedbackZone + ' .advert-display-feedback').hide();
            }, 1500);
        });

        $(document).on('click', '.advert-zone .advert-feedback-close', function () {
            setTimeout(function () {
                $('.advert-display-feedback').removeClass('advert-feedback-open');
            }, 250);
            setTimeout(function () {
                $('.advert-display-feedback').hide();
            }, 500);
        });

        $(window).on('scroll.advert', logging_ads);

    }); //onload



    function logging_ads() {

        $(this).off('scroll.advert')[0].setTimeout(function () {

            $('.advert-wrapper').each(function () {
                if (isVisible($(this).children()) && (viewed[$(this).attr('id') + $(this).data('ads')]) != 1 && $('#avbded581').length === 0) {
                    if ($(this).children()[0].nodeName === 'VIDEO') {
                        $(this).children()[0].play();
                    }
                    viewed[$(this).attr('id') + $(this).data('ads')] = 1;
                    var data = {
                        action: 'advert_view_log',
                        nonce: $(this).data('nonce'),
                        data: $(this).data('ads')
                    };
                    $.post(avajaxurl, data, function (response) { });
                }
            });

            jQuery(this).on('scroll.advert', logging_ads);

        }, 50);
    }

    function isVisible(elem) {
        var docViewTop = $(window).scrollTop();
        var docViewBottom = docViewTop + $(window).height();
        var docViewLeft = $(window).scrollLeft();
        var docViewRight = docViewLeft + $(window).width();

        var elemTop = elem.offset().top;
        var elemBottom = elemTop + elem.height();
        var elemLeft = elem.offset().left;
        var elemRight = elemLeft + elem.width();

        return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop) && (elemLeft >= docViewLeft) && (elemRight <= docViewRight));
    }

})(jQuery);

function advertVideoEnded(id) {
    var wrapID = id.replace("-video", "");
    var curHeight = document.getElementById(wrapID).offsetHeight;
    document.getElementById(wrapID).style.height=curHeight+'px';
    setTimeout(function(){
    document.getElementById(wrapID).style.height='0px';
    },1000)
}