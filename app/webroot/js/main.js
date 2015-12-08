$(function() {
    function getBaseURL() {
        var url = location.href;  // entire url including querystring - also: window.location.href;
        var baseURL = url.substring(0, url.indexOf('/', 14));


        if (baseURL.indexOf('http://localhost') != -1) {
            // Base Url for localhost
            var url = location.href;  // window.location.href;
            var pathname = location.pathname;  // window.location.pathname;
            var index1 = url.indexOf(pathname);
            var index2 = url.indexOf("/", index1 + 1);
            var baseLocalUrl = url.substr(0, index2);

            return baseLocalUrl + "/";
        }
        else {
            // Root Url for domain name
            return baseURL + "/";
        }

    }


    // Check maxlength of input, textarea
    $(document).on('blur', '[data-toggle=checklengh]', function() {
        var web_root = getBaseURL();
        var url = web_root + "tags/ajaxCheckCharacterSize";
        var val = $(this).val();
        var length = $(this).attr('maxlength');
        var $input = $(this);
//        var type = this.type || this.tagName.toLowerCase();
        $.ajax({
            type: 'POST',
            url: url,
            data: {character: val, maxlength: length},
            success: function(rs) {
                var obj = jQuery.parseJSON(rs);
                if (obj.result > obj.maxlength) {
                    alert("You have exceeded the maximum length of " + obj.maxlength);
                    $("#submit_button").attr("disabled", "disabled");
                    $("#quickAction-btn").hide();
                    $input.addClass('invalid');
                } else {
                    $("#submit_button").removeAttr("disabled");
                    $("#quickAction-btn").show();
                    $input.removeClass('invalid');
                }
            }
        });
    })

    // Reset all textbox auto complete
    //$(":input, form").attr('autocomplete', 'off');

    // Turnoff responsive
    //$('.container, .container-fluid').width($(window).width());

    // tooltip
    $("[data-toggle=tooltip], .Tooltip").tooltip();

    // popover
    $("[data-toggle=popover]").popover();
    $(document).on('click', '.popover-title .close', function(e) {
        var $target = $(e.target),
                $popover = $target.closest('.popover').prev();
        $popover && $popover.popover('hide');
    });

    var buildUrl = function(base, key, value) {
        var sep = (base.indexOf('?') > -1) ? '&' : '?';
        return base + sep + key + '=' + value;
    }

    // Check IE Browser
    function msieversion() {

        var ua = window.navigator.userAgent;
        var msie = ua.indexOf("MSIE ");

        if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))      // If Internet Explorer, return version number
            return true

        return false;
    }
    // ajax modal
    $(document).on('click', '[data-toggle="ajaxModal"]',
            function(e) {
                var self = $(this);
                $('#ajaxModal').remove();
                $('#ajaxModal').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                e.preventDefault();
                var $this = $(this),
                        $remote = $this.data('remote') || $this.attr('href'),
                        $modal = $('<div class="modal fade" id="ajaxModal" tabindex="-1" role="dialog"></div>'),
                        now = new Date().getTime();
                if (msieversion())
                    $remote = buildUrl($remote, '_t', now);
                $('body').append($modal);
                $modal.modal({
                    backdrop: 'static'
                });
                $modal.load($remote);
                $modal.on('hidden.bs.modal', function() {
                    if (self.attr('data-reload'))
                    {
                        location.reload();
                    }
                })
            }
    );

    $(document).on('focus', 'select', function() {
        $('.dropdown').removeClass('open');
    })

    var scrollDiv = $('<div class="scrollbar-measure"></div>').appendTo(document.body)[0],
            scrollBarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth;

    $(document).on('hidden.bs.modal', '.modal', function() {
        $('body').css('margin-right', '');
        if ($(this).attr('data-reload'))
        {
            location.reload();
        }

    }).on('show.bs.modal', '.modal', function() {
        // Bootstrap adds margin-right: 15px to the body to account for a
        // scrollbar, but this causes a "shift" when the document isn't tall
        // enough to need a scrollbar; therefore, we disable the margin-right
        // when it isn't needed.
        $('body').css('margin-right', scrollBarWidth + 'px');
    });

    $(document).on('click', '.formSubmit', function(e) {
        e.preventDefault();

        var $form = $(this).closest('form');

        if ($form.length)
        {
            $form.submit();
        }
    });

    

    function hasHtml5Validation() {
        //Check if validation supported && not safari
        return (typeof document.createElement('input').checkValidity === 'function') &&
                !(navigator.userAgent.search("Safari") >= 0 && navigator.userAgent.search("Chrome") < 0);
    }

    $(document).on('submit', 'form', function(e) {
        if (!hasHtml5Validation())
        {
            var isValid = true;
            var $inputs = $(this).find('[required]');
            $inputs.each(function() {
                var $input = $(this);
                $input.removeClass('invalid');
                if (!$.trim($input.val()).length)
                {
                    isValid = false;
                    $input.addClass('invalid');
                }
            });
            if (!isValid)
            {
                return false;
            }
        }
    });

    $(document).on('input change', 'input, select, textarea', function(e) {
        if (!hasHtml5Validation())
        {
            var $this = $(this);

            if ($this.prop('required'))
            {
                if ($this.val() != '')
                {
                    if ($this.hasClass('invalid'))
                        ;
                    {
                        $this.removeClass('invalid');
                    }
                }
                else
                {
                    $this.addClass('invalid');
                }
            }
        }
    });

    CheckAll($('input.CheckAll, a.CheckAll, label.CheckAll'));

    function CheckAll($control)
    {
        if ($control.is(':checkbox'))
        {
            var $target = $control.data('target') ? $($control.data('target')) : false,
                    $description = $control.data('description') ? $($control.data('description')).hide() : false;

            if (!$target || !$target.length)
            {
                $target = $control.closest('form');
            }

            var getCheckBoxes = function()
            {
                var $checkboxes,
                        filter = $control.data('filter');

                $checkboxes = filter
                        ? $target.find(filter).filter('input:checkbox')
                        : $target.find('input:checkbox');

                return $checkboxes;
            };

            var setSelectAllState = function()
            {
                var $checkboxes = getCheckBoxes(),
                        allSelected = $checkboxes.length > 0;

                $checkboxes.each(function() {
                    if ($(this).is($control))
                    {
                        return true;
                    }

                    if (!$(this).prop('checked'))
                    {
                        allSelected = false;
                        return false;
                    }
                });

                $control.prop('checked', allSelected);
            };
            setSelectAllState();

            var toggleAllRunning = false;

            $target.on('click', 'input:checkbox', function(e)
            {
                if (toggleAllRunning)
                {
                    return;
                }

                var $target = $(e.target);
                if ($target.is($control))
                {
                    return;
                }

                if ($control.data('filter'))
                {
                    if (!$target.closest($control.data('filter')).length)
                    {
                        return;
                    }
                }

                setSelectAllState();
            });

            $target.on('change', 'input:checkbox', function(e)
            {
                if ($description && $description.length)
                {
                    var checked = false,
                            $checkboxes = getCheckBoxes();

                    $checkboxes.each(function()
                    {
                        if ($(this).is(':checked'))
                        {
                            checked = true;
                        }
                    });

                    if (checked)
                    {
                        $description.show().removeClass('hide');
                    }
                    else
                    {
                        $description.hide().addClass('hide');
                    }
                }
            });

            $control.click(function(e)
            {
                if (toggleAllRunning)
                {
                    return;
                }

                toggleAllRunning = true;
                getCheckBoxes().prop('checked', e.target.checked).triggerHandler('click');
                toggleAllRunning = false;

                if ($description && $description.length)
                {
                    if ($(this).is(':checked'))
                    {
                        $description.show().removeClass('hide');
                    }
                    else
                    {
                        $description.hide().addClass('hide');
                    }
                }
            });
        }
        else
        {
            $control.click(function(e)
            {
                var target = $control.data('target');

                if (target)
                {
                    $(target).prop('checked', true);
                }
            });
        }
    }


});