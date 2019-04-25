jQuery(function () {

    // Check for cookie
    var repsite = getCookie("repsite");

    // Get domain - username, ignore www
    var domain = window.location.hostname;
    domain = domain.replace('www.', "");

    var is_safari = navigator.userAgent.indexOf("Safari") > -1;

    //if (is_safari && window.location.protocol == "http:") {

    // } else 
    if (window.location.protocol == "http:") {
        window.location = "https://" + domain + window.location.pathname;
    }

    var domainArr = domain.split('.');

    if (domainArr.length > 2) {
        var username = domainArr[0];
    }

    if (repsite) {
        try {
            repsite = JSON.parse(repsite)
        } catch (e) {
            repsite = {};
            repsite.username = "";
        };
        if (username && repsite.username != username) {
            getRepsiteHeader();
        } else {
            setRepsiteHeader(repsite);

            if (repsite.date) {
                var minutes = Math.floor((new Date() - new Date(repsite.date)) / 60000);

                if (minutes > 60)
                    getRepsiteHeader(); //data is older than an hour
            } else {
                getRepsiteHeader(); //no timestamp
            }
        }
    } else {
        getRepsiteHeader();

    }


    function getRepsiteHeader() {
        if (username) {
            jQuery.post(repsite_ajax.ajax_url, {
                _ajax_nonce: repsite_ajax.nonce,
                action: "repsite_header",
                username: username,
            }, function (data) {
                response = JSON.parse(data);
                response.date = new Date().toUTCString();
                if (response.error) {
                    if (repsite) { //we already have a dist, use it instead
                        setRepsiteHeader(repsite);
                    }
                } else {

                    setCookie("repsite", JSON.stringify(response), 7, domain);
                    setRepsiteHeader(response);
                }
            });
        }
    }


    function setRepsiteHeader(repsite) {
        if (repsite.name) {
            jQuery(".repsite-guestOf").each(function () {
                jQuery(this).html(jQuery(this).data('text'));
            });
            jQuery(".repsite-name").text(repsite.name);

            //remove Divi Header contact info if header is set.
            if (jQuery("#repsite-header").length) {
                jQuery("#et-info").empty();
            }

        }
        if (repsite.firestormcart && jQuery("#firestorm_repsite").length == 0) {
            //load Firestorm in iframe to set session for shopping cart and direct links.
            jQuery('body').append('<iframe id="firestorm_repsite" src="' + repsite.firestormcart + 'FirestormDefault.aspx?ID=' + repsite.distid + '" style="display:none;"></iframe>');
        }

        if (repsite.img && jQuery(".repsite-photo img").length == 0) {
            jQuery(".repsite-photo").append('<img src="' + repsite.img + '">');
        }

        if (repsite.email) {
            jQuery(".repsite-email").each(function () {
                jQuery(this).html(jQuery(this).data('text'));
                jQuery(this).append('<a href="mailto: ' + repsite.email + '" > ' + repsite.email + '</a>');
            });
        }

        if (repsite.phone) {
            jQuery(".repsite-phone").each(function () {
                jQuery(this).html(jQuery(this).data('text'));
                jQuery(this).append('<a href="tel: ' + repsite.phone + '" > ' + repsite.phone + '</a>');
            });
        }

    }

    function getCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return decodeURI(c.substring(name.length, c.length));
            }
        }
        return "";
    }

    function setCookie(cname, cvalue, exdays, domain) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + encodeURI(cvalue) + ";" + expires + ";domain=" + domain + ";path=/";
    }

});
