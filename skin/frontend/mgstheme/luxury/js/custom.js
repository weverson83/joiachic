function parallaxInit() {
    mgsjQuery('.parallax').parallax("30%", 0.1);
}

function initSlider(el, number, aplay, stophv, nav, pag, rtl) {
    mgsjQuery("#" + el).owlCarousel({
        rtl: rtl,
        items: number,
        loop: true,
        nav: nav,
        lazyLoad: true,
        dots: pag,
        autoplay: aplay,
        autoplayHoverPause: stophv,
        navText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
        responsive: {
            0: {items: 1},
            480: {items: 2},
            768: {items: 2},
            980: {items: number},
            1200: {items: number}
        }
    });
}
function initSliderShop(el, number, aplay, stophv, nav, pag, rtl) {
    mgsjQuery("#" + el).owlCarousel({
        rtl: rtl,
        items: number,
        loop: true,
        nav: nav,
        lazyLoad: true,
        dots: pag,
        autoplay: aplay,
        autoplayHoverPause: stophv,
        navText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
        responsive: {
            0: {items: 1},
            480: {items: 2},
            768: {items: 2},
            980: {items: 3},
            1200: {items: number}
        }
    });
}
function initSliderFeaturedBrand(el, number, aplay, stophv, nav, pag, rtl) {
    mgsjQuery("#" + el).owlCarousel({
        items: number,
        rtl: rtl,
        loop: true,
        lazyLoad: true,
        nav: nav,
        dots: pag,
        autoplay: aplay,
        autoplayHoverPause: stophv,
        navText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
        responsive: {
            0: {items: 1},
            480: {items: 2},
            768: {items: 3},
            980: {items: number},
            1200: {items: number}
        }
    });
}
function initSliderProduct(el, number, aplay, stophv, nav, pag, rtl) {
    mgsjQuery("#" + el).owlCarousel({
        items: number,
        rtl: rtl,
        loop: true,
        lazyLoad: true,
        nav: nav,
        dots: pag,
        autoplay: aplay,
        autoplayHoverPause: stophv,
        navText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
        responsive: {
            0: {items: 1},
            480: {items: 1},
            768: {items: 2},
            980: {items: 3},
            1200: {items: 3}
        }
    });
}
function initSliderBanner(el, number, aplay, stophv, nav, pag, rtl) {
    mgsjQuery("#" + el).owlCarousel({
        rtl: rtl,
        items: number,
        loop: true,
        lazyLoad: true,
        nav: nav,
        dots: pag,
        autoplay: aplay,
        autoplayHoverPause: stophv,
        navText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
        responsive: {
            0: {items: 1},
            480: {items: 1},
            768: {items: 1},
            980: {items: number},
            1200: {items: number}
        }
    });
}
function initSliderBrand(el, number, aplay, stophv, nav, pag, rtl) {
    mgsjQuery("#" + el).owlCarousel({
        rtl: rtl,
        items: number,
        loop: true,
        lazyLoad: true,
        nav: nav,
        dots: pag,
        autoplay: aplay,
        autoplayHoverPause: stophv,
        navText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
        responsive: {
            0: {items: 1},
            480: {items: 1},
            768: {items: 3},
            980: {items: number},
            1200: {items: number}
        }
    });
}

function socialSlider(el, number, rtl) {
    mgsjQuery("#" + el).owlCarousel({
        rtl: rtl,
        items: number,
        loop: true,
        lazyLoad: true,
        nav: true,
        dots: false,
        autoplay: true,
        autoplayHoverPause: true,
        navText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
        responsive: {
            0: {items: 1},
            480: {items: 1},
            768: {items: 2},
            980: {items: number},
            1200: {items: number}
        }
    });
}
function toggleEl(el) {
    //mgsjQuery('.toggle-el').hide();
    mgsjQuery('#' + el).slideToggle('fast');
}

function initThemeJs() {
    // init tooltip
    mgsjQuery('.tooltip-links').tooltip({
        selector: "[data-toggle=tooltip]",
        container: "body"
    });

    // init height for product info box
    /* if(mgsjQuery(window).width() > 991) {
     mgsjQuery(".product-info-box").css("min-height", "auto");
     mgsjQuery(".products-grid").each(function() {
     var wrapper = $(this);
     var minBoxHeight = 0;
     mgsjQuery(".product-info-box", wrapper).each(function() {
     if(mgsjQuery(this).height() > minBoxHeight)
     minBoxHeight = mgsjQuery(this).height();
     });
     mgsjQuery(".product-info-box", wrapper).height(minBoxHeight);
     });
     } else {
     mgsjQuery(".product-info-box").css("min-height", "auto");
     } */
}
function hideAlert() {
    mgsjQuery('.alert').slideUp(1000);
}
mgsjQuery(window).load(function () {
    mgsjQuery('.category_home').show();
    setTimeout(hideAlert, 2000);
    mgsjQuery(window).bind('body', function () {
        parallaxInit();
    });

    var $container = mgsjQuery('.masonry-grid');
    // initialize
    $container.masonry({
        itemSelector: '.item'
    });

    initThemeJs();

    if (mgsjQuery('.scroll-to-top').length) {
        mgsjQuery(window).scroll(function () {
            if (mgsjQuery(this).scrollTop() > 1) {
                mgsjQuery('.scroll-to-top').css({bottom: "25px"});
            } else {
                mgsjQuery('.scroll-to-top').css({bottom: "-100px"});
            }
        });

        mgsjQuery('.scroll-to-top').click(function () {
            mgsjQuery('html, body').animate({scrollTop: '0px'}, 800);
            return false;
        });
    }

});
mgsjQuery(document).ready(function () {

    if (mgsjQuery(window).width() < 767) {
        mgsjQuery('.middle-footer .middle-block .block-title').click(function () {
            mgsjQuery(this).parent().find('.block-content').slideToggle('fast');
            if (mgsjQuery(this).hasClass('is-show')) {
                mgsjQuery(this).removeClass('is-show');
            } else {
                mgsjQuery(this).addClass('is-show');
            }
        });

    }

    mgsjQuery(".product-essential .product-shop .go-rate").click(function (event) {
        event.preventDefault();
        mgsjQuery("#product_tabs .tab-content .active").removeClass("active");
        mgsjQuery("#product_tabs .nav-tabs .active").removeClass("active");
        mgsjQuery("#product_tabs #box-reviews").addClass("active");
        mgsjQuery(".product-view #accordion .panel-collapse").removeClass("in");
        mgsjQuery(".product-view #accordion #collapse_reviews").addClass("in");
        mgsjQuery("#product_tabs #reviews-tab").addClass("active");
        mgsjQuery('html, body').animate({
            scrollTop: mgsjQuery("#product_tabs").offset().top
        }, 1000);
        mgsjQuery('html, body').animate({
            scrollTop: mgsjQuery(".product-view #accordion").offset().top
        }, 1000);
    });
    mgsjQuery(".postWrapper-detail .post-info .timer").click(function (event) {
        event.preventDefault();
        mgsjQuery('html, body').animate({
            scrollTop: mgsjQuery("#post-comment-box").offset().top
        }, 800);
    });
    mgsjQuery(".category-tabs .tab-menu li a").click(function () {
        mgsjQuery(this).parent().parent().parent().find('.tab-pane').removeClass("ready");
        var numberClick = mgsjQuery(this).attr("data-number");
        for (i = 1; i < numberClick; i++) {
            if (i == numberClick) {
                return false;
            }
            mgsjQuery(this).parent().parent().parent().find('.tab-pane' + i).addClass("ready");
        }
    });
    if (mgsjQuery('.filter-content').parent().hasClass('showct')) {
        mgsjQuery(this).find('fa').addClass('fa-plus');
        mgsjQuery(this).find('fa').addClass('fa-minus');
    }
    mgsjQuery('.filter-content .hide-filter').click(function () {
        mgsjQuery(this).parent().slideToggle('fast');
    });
    mgsjQuery(".question-list .item").click(function () {
        if (mgsjQuery(this).hasClass('active')) {
            mgsjQuery(this).find('.fa').removeClass("fa-chevron-down");
            mgsjQuery(this).find('.fa').addClass("fa-chevron-up");
        } else {
            mgsjQuery(this).find('.fa').removeClass("fa-chevron-up");
            mgsjQuery(this).find('.fa').addClass("fa-chevron-down");
        }
    });
    mgsjQuery(window).scroll(function () {
        if (mgsjQuery(this).scrollTop() > 200 & mgsjQuery(this).width() > 991) {
            mgsjQuery('.header-v1.sticky-menu .header-content').addClass('sticky-content');
            mgsjQuery('.header-v3.sticky-menu .header-menu').addClass('sticky-content');
            mgsjQuery('.header-v3.sticky-menu .header-content').addClass('sticky-logo');
            mgsjQuery('.header-v2').addClass('fixed-bottom');
            mgsjQuery('.header-v4.sticky-menu .middle-header').addClass('sticky-content');
        } else {
            mgsjQuery('.header-v1.sticky-menu .header-content').removeClass('sticky-content');
            mgsjQuery('.header-v3.sticky-menu .header-menu').removeClass('sticky-content');
            mgsjQuery('.header-v3.sticky-menu .header-content').removeClass('sticky-logo');
            mgsjQuery('.header-v2').removeClass('fixed-bottom');
            mgsjQuery('.header-v4.sticky-menu .middle-header').removeClass('sticky-content');
        }
    });

    mgsjQuery('.btn-responsive-nav').click(function () {
        mgsjQuery('.nav-main').addClass('show-menu');
    });
    mgsjQuery('nav.nav-main .fa-times').click(function () {
        mgsjQuery('.nav-main').removeClass('show-menu');
    });
    mgsjQuery('.mega-menu-item ul.dropdown-menu .level1 .toggle-menu .fa-plus').click(function () {
        mgsjQuery(this).parent().siblings('ul').slideDown('fade');
        mgsjQuery(this).addClass('hide-plus');
        mgsjQuery(this).siblings('.fa-minus').addClass('show-minus');
    });

    mgsjQuery('.mega-menu-item ul.dropdown-menu .level1 .toggle-menu .fa-minus').click(function () {
        mgsjQuery(this).parent().siblings('ul').slideUp('fade');
        mgsjQuery(this).siblings('.fa-plus').removeClass('hide-plus');
        mgsjQuery(this).removeClass('show-minus');
    });

    mgsjQuery('.static-menu .dropdown-submenu .toggle-menu .fa-plus').click(function () {
        mgsjQuery(this).parent().siblings('ul').slideDown('fade');
        mgsjQuery(this).addClass('hide-plus');
        mgsjQuery(this).siblings('.fa-minus').addClass('show-minus');
    });

    mgsjQuery('.static-menu .dropdown-submenu .toggle-menu .fa-minus').click(function () {
        mgsjQuery(this).parent().siblings('ul').slideUp('fade');
        mgsjQuery(this).siblings('.fa-plus').removeClass('hide-plus');
        mgsjQuery(this).removeClass('show-minus');
    });
    var side_header2 = mgsjQuery("#header-v2").innerHeight();
    var content_height2 = mgsjQuery("#maincontent").innerHeight();
    if (side_header2 > content_height2) {
        mgsjQuery(".cms-index-index header").css("height", side_header2 + "px");
        mgsjQuery("body").not(".cms-index-index").css("height", side_header2 + "px");
    }
    mgsjQuery('.product-top').click(function () {
        mgsjQuery('.product-top').removeClass('hide-cover');
        mgsjQuery(this).addClass('hide-cover');
    })
    /* end */
    mgsjQuery(window).scroll(function () {
        var side_header_height2 = mgsjQuery("#header-v2").innerHeight();
        var window_height = mgsjQuery(this).height();

        if (side_header_height2 - window_height < mgsjQuery(this).scrollTop()) {
            if (!mgsjQuery("#header-v2").hasClass("fixed-bottom")) {
                mgsjQuery("#header-v2").addClass("fixed-bottom");
            }
        }
        if (side_header_height2 - window_height >= mgsjQuery(this).scrollTop()) {
            if (mgsjQuery("#header-v2").hasClass("fixed-bottom")) {
                mgsjQuery("#header-v2").removeClass("fixed-bottom");
            }
        }
    });
});
mgsjQuery(window).resize(function () {
    var side_header = mgsjQuery("#header-v2").innerHeight();
    var content_height = mgsjQuery("#maincontent").innerHeight();
    if (side_header > content_height) {
        mgsjQuery(".cms-index-index header").css("height", side_header + "px");
        mgsjQuery("body").not(".cms-index-index").css("height", side_header + "px");
    }
});
function showHideFilter(vari) {
    mgsjQuery('.filter-content' + vari).slideToggle('fast');
    if (mgsjQuery('.hide-filter' + vari + ' .fa').hasClass('fa-plus')) {
        mgsjQuery('.hide-filter' + vari + ' .fa').removeClass('fa-plus');
        mgsjQuery('.hide-filter' + vari + ' .fa').addClass('fa-minus');
    } else {
        mgsjQuery('.hide-filter' + vari + ' .fa').removeClass('fa-minus');
        mgsjQuery('.hide-filter' + vari + ' .fa').addClass('fa-plus');
    }
}
// init gmap
function initGmap(address, html, image) {
    mgsjQuery.ajax({
        type: "GET",
        dataType: "json",
        url: "https://maps.googleapis.com/maps/api/geocode/json",
        data: {'address': address, 'sensor': false},
        success: function (data) {
            if (data.results.length) {
                latitude = data.results[0].geometry.location.lat;
                longitude = data.results[0].geometry.location.lng;

                var locations = [
                    [html, latitude, longitude, 2]
                ];

                var map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 14,
                    scrollwheel: false,
                    navigationControl: true,
                    mapTypeControl: false,
                    scaleControl: false,
                    draggable: true,
                    center: new google.maps.LatLng(latitude, longitude),
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                });

                var infowindow = new google.maps.InfoWindow();

                var marker, i;

                for (i = 0; i < locations.length; i++) {

                    marker = new google.maps.Marker({
                        position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                        map: map,
                        icon: image
                    });


                    google.maps.event.addListener(marker, 'click', (function (marker, i) {
                        return function () {
                            infowindow.setContent(locations[i][0]);
                            infowindow.open(map, marker);
                        }
                    })(marker, i));
                }
            }
        }
    });
}

var newCount = 2;
var hotCount = 2;
var featuredCount = 2;
var saleCount = 2;
var rateCount = 2;
// load more products
function loadMore(count, type, productCount, perRow) {
    mgsjQuery('#' + type + '_loadmore_button .loading').show();
    var request = new Ajax.Request(WEB_URL + 'mpanel/loadmore/' + type + '?perrow=' + perRow + '&p=' + count + '&limit=' + productCount, {
        onSuccess: function (response) {
            result = response.responseText;
            mgsjQuery('#' + type + '_product_container').append(result);
            mgsjQuery('#' + type + '_loadmore_button .loading').hide();

            initThemeJs();
        }
    });
}

// open overlay popup
function openOverlay() {
    mgsjQuery('#theme-popup').show();
}

// close overlay popup
function closeOverlay() {
    mgsjQuery('#theme-popup').hide();
}

var active = false;
var data = "";

// Price slider
function sliderAjax(url) {
    if (!active) {
        active = true;
        openOverlay();
        oldUrl = url;
        try {
            mgsjQuery.ajax({
                url: url,
                dataType: 'json',
                type: 'post',
                data: data,
                success: function (data) {
                    if (data.leftcontent) {
                        if (mgsjQuery('.block-layered-nav')) {
                            mgsjQuery('.block-layered-nav').empty();
                            mgsjQuery('.block-layered-nav').append(data.leftcontent);
                        }
                    }
                    if (data.maincontent) {
                        mgsjQuery('#product-list-container').empty();
                        mgsjQuery('#product-list-container').append(data.maincontent);
                    }
                    var hist = url.split('?');
                    if (window.history && window.history.pushState) {
                        window.history.pushState('GET', data.title, url);
                    }
                    initThemeJs();
                    closeOverlay();
                }
            });
        } catch (e) {
        }

        active = false;
    }
    return false;
}


// Ajax catalog load
function shopMore(url) {
    oldHtml = mgsjQuery('.category-products ul.products-grid').html();
    oldHtml_l = mgsjQuery('.category-products #products-list').html();
    openOverlay();
    oldUrl = url;
    try {
        mgsjQuery.ajax({
            url: url,
            dataType: 'json',
            type: 'post',
            data: data,
            success: function (data) {
                if (data.leftcontent) {
                    if (mgsjQuery('.block-layered-nav')) {
                        mgsjQuery('.block-layered-nav').empty();
                        mgsjQuery('.block-layered-nav').append(data.leftcontent);
                    }
                }
                if (data.maincontent) {
                    mgsjQuery('#product-list-container').empty();
                    mgsjQuery('#product-list-container').append(data.maincontent);
                    mgsjQuery('.category-products ul.products-grid').prepend(oldHtml);
                    mgsjQuery('.category-products #products-list').prepend(oldHtml_l);
                }
                initThemeJs();
                closeOverlay();
            }
        });
    } catch (e) {
    }
}

function setTabBackground(url) {
    $('tab-background').setStyle({backgroundImage: 'url(' + url + ')'});
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ')
            c = c.substring(1);
        if (c.indexOf(name) == 0)
            return c.substring(name.length, c.length);
    }
    return "";
}

function dontShowPopup(el) {
    if ($(el).checked == true) {
        var d = new Date();
        d.setTime(d.getTime() + (24 * 60 * 60 * 1000 * 365));
        var expires = "expires=" + d.toUTCString();
        document.cookie = 'newsletterpopup' + "=" + 'nevershow' + "; " + expires;
    } else {
        document.cookie = 'newsletterpopup' + "= ''; -1";
    }


}
function closeMgs() {
    mgsjQuery.magnificPopup.close();
}