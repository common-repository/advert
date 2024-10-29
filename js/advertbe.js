(function ($) {

    $(document).ready(function () {
        curWindow = window.location.href;

        //hide submenus for aesthetics
        hidesubmenus();

        //screen reader
        //advert dashboard
        if (curWindow.indexOf("page=advert") >= 0 && curWindow.indexOf("page=advert-") == -1 || curWindow.indexOf("page=advert-user") >= 0) {
            $('#adv-settings').prepend($('#advert-dashboard-meta-prefs-workaround').html());
        }

        //advert analysis drilldown
        if (curWindow.indexOf("page=advert-analysis-drilldown") >= 0) {
            $('#show-settings-link').text('Analysys Options');
            $('#screen-options-wrap').prepend($('#advert-analysis-drilldown-meta-prefs-workaround').html());
            $('#advert-analysis-drilldown-meta-prefs-workaround').remove();
        }

        //datepicker
        if ($('.adv_datetimepicker').length) {
            $('.adv_datetimepicker').datepicker();
        }

        //add highlight to parent of tabs
        if (curWindow.indexOf("advert-analysis") >= 0) {
            $('#toplevel_page_advert ul li').eq(6).addClass('current');
            $('#toplevel_page_advert-user ul li').eq(4).addClass('current');
        }

        if (curWindow.indexOf("advert-cp") >= 0) {
            $('#toplevel_page_advert ul li').eq(8).addClass('current');
        }

        //advertiser
        if ($('body').hasClass('post-type-advert-advertiser') && !$('body').hasClass('toplevel_page_advert-user')) {
            if ($('#toplevel_page_advert').length) { $userPage = '#toplevel_page_advert'; $('#toplevel_page_advert ul li').eq(2).addClass('current'); }
            $($userPage).removeClass('wp-not-current-submenu');
            $($userPage).addClass('wp-has-current-submenu wp-menu-open');
            $($userPage + ' a:first').removeClass('wp-not-current-submenu');
            $($userPage + ' a:first').addClass('wp-has-current-submenu wp-menu-open');
        }

        if ($('#menu-posts-advert-banner').hasClass('wp-menu-open')) {
            if ($('#toplevel_page_advert-user').length) { $userPage = '#toplevel_page_advert-user'; $('#toplevel_page_advert-user ul li').eq(2).addClass('current'); }
            if ($('#toplevel_page_advert').length) { $userPage = '#toplevel_page_advert'; $('#toplevel_page_advert ul li').eq(3).addClass('current'); }
            $($userPage).removeClass('wp-not-current-submenu');
            $($userPage).addClass('wp-has-current-submenu wp-menu-open');
            $($userPage + ' a:first').removeClass('wp-not-current-submenu');
            $($userPage + ' a:first').addClass('wp-has-current-submenu wp-menu-open');
        }

        if ($('#menu-posts-advert-campaign').hasClass('wp-menu-open')) {
            if ($('#toplevel_page_advert-user').length) { $userPage = '#toplevel_page_advert-user'; $('#toplevel_page_advert-user ul li').eq(3).addClass('current'); }
            if ($('#toplevel_page_advert').length) { $userPage = '#toplevel_page_advert'; $('#toplevel_page_advert ul li').eq(4).addClass('current'); }
            $($userPage).removeClass('wp-not-current-submenu');
            $($userPage).addClass('wp-has-current-submenu wp-menu-open');
            $($userPage + ' a:first').removeClass('wp-not-current-submenu');
            $($userPage + ' a:first').addClass('wp-has-current-submenu wp-menu-open');
        }

        //location
        if ($('body').hasClass('post-type-advert-location')) {
            if ($('#toplevel_page_advert').length) { $userPage = '#toplevel_page_advert'; $('#toplevel_page_advert ul li').eq(5).addClass('current'); }
            $($userPage).removeClass('wp-not-current-submenu');
            $($userPage).addClass('wp-has-current-submenu wp-menu-open');
            $($userPage + ' a:first').removeClass('wp-not-current-submenu');
            $($userPage + ' a:first').addClass('wp-has-current-submenu wp-menu-open');
        }


        $('.post-type-advert-banner .advert-media-video-button').click(function (e) {
            e.preventDefault();
            //checks the click type
            if (e.originalEvent !== undefined) {
                advert_video_init('.media-input', '.advert-media-video-button');
            }
        });


        var advert_video_init = function (selector, button_selector) {
            var clicked_button = false;
            var video_id;
            var url;
            $(selector).each(function (i, input) {
                clicked_button = $(this);

                // check for media manager instance
                if (wp.media.frames.advert_frame) {
                    wp.media.frames.advert_frame.open();
                    return;
                }

                // configuration of the media manager new instance
                wp.media.frames.advert_frame = wp.media({
                    title: $('.advert-banner-translations1').text(),
                    multiple: false,
                    library: {
                        type: 'video'
                    },
                    button: {
                        text: $('.advert-banner-translations2').text()
                    }
                });

                // Function used for the video selection and media manager closing
                var advert_media_set_video = function () {
                    var selection = wp.media.frames.advert_frame.state().get('selection');

                    // no selection
                    if (!selection) {
                        return;
                    }

                    if (video_id && url) {
                        $('.media-video-src').remove();
                        $('#advert_video_id').val(video_id);
                        $('#advert_video_url').val(url);
                        $('#advert_video_title').val(filename);
                        $('.advert-media-video-button').hide();
                        $('.advert-media-video-remove-button').show();
                        $('#postvideodiv video').remove();
                        $('#postvideodiv-aqs video').remove();
                        $('#postvideodiv .inside').prepend('<video class="advert-video-preview" width="100%" controls><source src="' + url + '" type="video/mp4" /></video>');
                        $('.advert-video-title').text(filename);
                    }

                    // iterate through selected elements
                    selection.each(function (attachment) {
                        video_id = attachment.attributes.id;
                        url = attachment.attributes.url;
                        filename = attachment.attributes.filename;
                    });
                };


                // closing event for media manger
                wp.media.frames.advert_frame.on('close', advert_media_set_video);
                // video selection event
                wp.media.frames.advert_frame.on('select', advert_media_set_video);
                // showing media manager
                wp.media.frames.advert_frame.open();
            });
        };


        $('.post-type-advert-banner .advert-media-video-remove-button').click(function (e) {
            e.preventDefault();
            $('.advert-video-title').text('');
            $('#postvideodiv video').remove();
            $('#postvideodiv-aqs video').remove();
            $('#advert_video_id').val('advertremovevideo');
            $('#advert_video_url').val('advertremovevideo');
            $('#advert_video_title').val('advertremovevideo');
            $(this).hide();
            $('.advert-media-video-button').show();
        });


        $('.advert_page_user_banner .show-new-banner').click(function () {
            $('.advert_page_user_banner #add-new form').slideDown('slow');
            $('.advert_page_user_banner #add-new').addClass('addnew-show');
        });

        $('.advert_page_user_banner .hide-new-banner').click(function () {
            $('.advert_page_user_banner #add-new').removeClass('addnew-show');
            $('.advert_page_user_banner #add-new form').slideUp('slow');
        });

        $('.advert_page_user_campaign .show-new-campaign').click(function () {
            $('.advert_page_user_campaign #add-new form').slideDown('slow')
            $('.advert_page_user_campaign #add-new').addClass('addnew-show');
        });

        $('.advert_page_user_campaign .hide-new-campaign').click(function () {
            $('.advert_page_user_campaign #add-new').removeClass('addnew-show');
            $('.advert_page_user_campaign #add-new form').slideUp('slow');
        });


        //analysis table
        $('#aae-table tr').click(function () {
            if ($(this).hasClass('aae-highlight')) { $(this).removeClass('aae-highlight'); }
            else { $(this).addClass('aae-highlight'); }
        });


        $(window).resize(function () {
            if ($('#chart_div1').length) {
                drawChart1();
            }
        });


        //User add funds
        $('.advert-show-add-funds').click(function (e) {
            e.preventDefault();
            $('.advert-show-add-funds').hide();
            $('.advert-add-funds').show();
        });


        if ($('body').hasClass('post-type-advert-banner') && $('body').hasClass('post-new-php')) {
            $('#title').prop('required', true);
        }

        if ($('body').hasClass('post-type-advert-campaign') && $('body').hasClass('post-new-php')) {
            $('#title').prop('required', true);
        }

        if ($('body').hasClass('post-type-advert-location') && $('body').hasClass('post-new-php')) {
            $('#title').prop('required', true);
        }


        //pricing model change      
        $('.campaign_price_model').change(function () {
            if ($(this).attr('value') == 'cpp') {
                $('#campaign_budget option[value="per_day"]').attr('disabled', true);
                $('#campaign_budget').val('fixed');
            }
            else {
                $('#campaign_budget option[value="per_day"]').removeAttr('disabled');
            }
        });


        //atach text lenth if set
        $('#banner_location').change(function () {
            if ($(this).find(':selected').attr('data-textchar')) {
                $textchar = $(this).find(':selected').attr('data-textchar').split('-|-');
                $('#banner_text_ad1').attr('maxlength', $textchar[0]);
                $('#banner_text_ad2').attr('maxlength', $textchar[1]);
            }
            else {
                $('#banner_text_ad1').removeAttr('maxlength');
                $('#banner_text_ad2').removeAttr('maxlength');
            }
        });


        //atach image and video sizes if set
        $('#banner_location').change(function () {
            if ($(this).find(':selected').attr('data-videolength') != '') {
                $videolength = $(this).find(':selected').attr('data-videolength');
                if ($('.banner-video-length').length) {
                    $('.banner-video-length').text($videolength + ' seconds');
                }
                else {
                    $('#postvideodiv .advert-media-video-button').after('<p class="hide-if-no-js"><span class="advert-sm-info"><br/>' + $('.advert-banner-translations5').text() + ':</span><span class="advert-sm-info banner-video-length">' + $videolength + '&nbsp;' + $('.advert-banner-translations6').text() + '</span></p>');
                }
            }
            else {
                $('span.banner-video-length').prev().remove();
                $('span.banner-video-length').remove();
            }

            if ($(this).find(':selected').attr('data-imagedimensions') != '') {
                $imagedimensions = $(this).find(':selected').attr('data-imagedimensions').split('-|-');
                if ($('.banner-image-dimensions').length) {
                    $('.banner-image-dimensions').text('Width: ' + $imagedimensions[0] + 'px | ' + 'Height: ' + $imagedimensions[1] + 'px');
                }
                else {
                    $('#postimagediv hr').before('<p class="hide-if-no-js"><span class="advert-sm-info">' + $('.advert-banner-translations7').text() + ':</span><span class="advert-sm-info banner-image-dimensions">' + $('.advert-banner-translations8').text() + ':&nbsp;' + $imagedimensions[0] + 'px&nbsp;|&nbsp;' + $('.advert-banner-translations9').text() + ': ' + $imagedimensions[1] + 'px</span></p>');
                }
            }
            else {
                $('span.banner-image-dimensions').prev().remove();
                $('span.banner-image-dimensions').remove();
            }
        });

        //open rates data if available
        $('.advert-open-rates-data a').click(function (e) {
            e.preventDefault();
            var popup = window.open("", "", "width=400,height=580,resizeable,scrollbars"),
            table = document.getElementById("advert-current-banner-rates");
            popup.document.write(table.innerHTML);
            popup.document.title = 'Current Rates';
            popup.document.close();
        });

        //open analysis data if available
        $('.advert-open-raw-data').click(function (e) {
            e.preventDefault();
            var popup = window.open("", "", "width=1178,height=580,resizeable,scrollbars"),
            table = document.getElementById("aae-table");
            popup.document.write('<html><head><title>AdVert</title><style type="text/css">table{border-collapse:collapse;margin:0 auto;}table, td, th{border:1px solid black;}</style></head><body>');
            popup.document.write(table.outerHTML);
            popup.document.write('</body></html>');
            popup.document.close();
        });

        //add advert logo to screens
        $('.post-type-advert-banner .wrap').prepend('<div class="advert-page-heading-logo">a</div>');
        if (!$('body').hasClass('toplevel_page_advert-user')) {
            $('.post-type-advert-advertiser .wrap').prepend('<div class="advert-page-heading-logo">a</div>');
        }
        $('.post-type-advert-campaign .wrap').prepend('<div class="advert-page-heading-logo">a</div>');
        $('.post-type-advert-location .wrap').prepend('<div class="advert-page-heading-logo">a</div>');
        setTimeout(function () {
            $('.advert-page-heading-logo').addClass('showlogo');
        }, 400);


        var welcomePanel = $('#advert-welcome-panel'),
		welcomePanelHide = $('#advert_welcome_panel-hide'),
		updateWelcomePanel;

        updateWelcomePanel = function (visible) {
            $.post(ajaxurl, {
                action: 'advert_welcome_message',
                visible: visible,
                advertwelcomepanelnonce: $('#advertwelcomepanelnonce').val()
            });
        };

        if (welcomePanel.hasClass('hidden') && welcomePanelHide.prop('checked')) {
            welcomePanel.removeClass('hidden');
        }

        $('.advert-welcome-panel-close', welcomePanel).click(function (e) {
            e.preventDefault();
            welcomePanel.addClass('hidden');
            updateWelcomePanel(0);
            $('#advert_welcome_panel-hide').prop('checked', false);
        });

        welcomePanelHide.click(function () {
            welcomePanel.toggleClass('hidden', !this.checked);
            updateWelcomePanel(this.checked ? 1 : 0);
        });


        //advert control panel warnings
        $('.advert-cp-warning').change(function () {
            if ($(this).is(":checked")) {
                $(this).closest('tr').addClass('advert-cp-warning-wrap');
                $(this).parent().find('span.advert-span-info').show();
            }
            else {
                $(this).closest('tr').removeClass('advert-cp-warning-wrap');
                $(this).parent().find('span.advert-span-info').hide();
            }
        });


        if ($('#advert_category-adder').length > 0) {
            $url = $('#advert-admin-url a').attr('href');
            $('#taxonomy-advert_category').append('<hr><div style="text-align:center;"><strong><a href="' + $url + '">' + $('.advert-banner-translations10').text() + '</a></strong></div>');
        }

        //unhide the chart on the analysis drilldown page
        if ($('#chart_div1').length > 0) {
            setTimeout(function () {
                $('#chart_div1').height('200px');
            }, 1500);
        }

        $('#message .advert-notice-dismiss').click(function () {
            $(this).parent().hide();
        });


        $("#advert-lightbox").fadeIn("slow");

        $(".advert-lightbox-close").click(function () { $("#advert-lightbox").fadeOut(); });

        $("#advert_price").focus(function () {
            $('#transaction_reason').show();
            reason = false;
        });


        // Export CSV file
        $(".advert-export-csv").on('click', function (event) {
            exportTableToCSV.apply(this, [$('#aae-table'), 'advert_export.csv']);
        });


    }); //onload



    function exportTableToCSV($table, filename) {

        var $rows = $table.find('tr:has(td)'),

        // Temporary delimiter characters unlikely to be typed by keyboard
        // This is to avoid accidentally splitting the actual contents
            tmpColDelim = String.fromCharCode(11), // vertical tab character
            tmpRowDelim = String.fromCharCode(0), // null character

        // actual delimiter characters for CSV format
            colDelim = '","',
            rowDelim = '"\r\n"',

        // Grab text from table into CSV formatted string
            csv = '"' + $rows.map(function (i, row) {
                var $row = $(row),
                    $cols = $row.find('td');

                return $cols.map(function (j, col) {
                    var $col = $(col),
                        text = $col.text();

                    return text.replace(/"/g, '""'); // escape double quotes

                }).get().join(tmpColDelim);

            }).get().join(tmpRowDelim)
                .split(tmpRowDelim).join(rowDelim)
                .split(tmpColDelim).join(colDelim) + '"',

        // Data URI
            csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);

        $(this)
            .attr({
                'download': filename,
                'href': csvData,
                'target': '_blank'
            });
    }


    function hidesubmenus() {
        $('#toplevel_page_advert .wp-submenu-wrap li:nth-child(8)').hide();
        $('#toplevel_page_advert .wp-submenu-wrap li:nth-child(10)').hide();
        $('#toplevel_page_advert .wp-submenu-wrap li:nth-child(11)').hide();
        $('#toplevel_page_advert .wp-submenu-wrap li:nth-child(12)').hide();
        $('#toplevel_page_advert_user .wp-submenu-wrap li:nth-child(6)').hide();
        //$('#toplevel_page_advert_user .wp-submenu-wrap li:nth-child(7)').hide();
    }


})(jQuery);