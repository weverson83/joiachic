/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    design
 * @package     default_iphone
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

 // Homepage categories and subcategories slider
document.observe("dom:loaded", function() {

    transEndEventNames = {
        'WebkitTransition' : 'webkitTransitionEnd',
        'MozTransition'    : 'transitionend',
        'OTransition'      : 'oTransitionEnd',
        'msTransition'     : 'MSTransitionEnd',
        'transition'       : 'transitionend'
    },
    transEndEventName = transEndEventNames[ Modernizr.prefixed('transition') ];

    function handler(position) {
        var lat = position.coords.latitude,
            lng = position.coords.longitude;

        //alert(latitude + ' ' + longitude);

        var geocoder = new google.maps.Geocoder();

        function codeLatLng() {
            var latlng = new google.maps.LatLng(lat, lng);
            geocoder.geocode({'latLng': latlng}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                        alert(results[0].formatted_address);
                    }
                } else {
                    alert("Geocoder failed due to: " + status);
                }
            });
        }

        //codeLatLng();

    }

    var loadMore = Class.create({
        initialize: function (list, href, pattern) {
            var that = this;

            this.list = list;
            this.list.insert({ after : '<div class="more"><span id="more_button" class="more-button">More</span></div>'});
            this.href = href.readAttribute('href');
            this.button = $('more_button');
            this.holder = new Element('div', { 'class': 'response-holder' });

            this.button.observe('click', function () {
                if ( !that.button.hasClassName('loading') ) {
                    new Ajax.Request(that.href, {
                        onCreate: function () {
                            that.button.addClassName('loading');
                        },
                        onComplete: function(response) {
                            if (200 == response.status) {
                                that.holder.innerHTML = response.responseText;
                                that.holder.select(pattern).each(function(elem) {
                                    that.list.insert({ bottom : elem });
                                });
                                that.href = that.holder.select('.next-page')[0].readAttribute('href');
                                that.button.removeClassName('loading');
                                if ( !that.href ) {
                                    that.button.up().remove();
                                }
                            }

                        }
                    });
                }
            });
        }
    });

    if ( $$('.c-list')[0] && $$('.next-page')[0]  ) {
        var loadMoreCategory = new loadMore(
            $$('.c-list')[0],
            $$('.next-page')[0],
            '.c-list > li'
        )
    }

    if ( $$('.downloadable-products-history .list')[0] && $$('.next-page')[0]  ) {
        var loadMoreCategory = new loadMore(
            $$('.downloadable-products-history .list')[0],
            $$('.next-page')[0],
            '.downloadable-products-history .list > li'
        )
    }

    if ( $$('.review-history .list')[0] && $$('.next-page')[0]  ) {
        var loadMoreCategory = new loadMore(
            $$('.review-history .list')[0],
            $$('.next-page')[0],
            '.review-history .list > li'
        )
    }

    if ( $$('.recent-orders .data-table')[0] && $$('.next-page')[0]  ) {
        var loadMoreCategory = new loadMore(
            $$('.recent-orders .data-table')[0],
            $$('.next-page')[0],
            '.recent-orders .data-table tbody > tr'
        )
    }

    //-----------------------------//

    $$('label[for]').each(function(label) {
        label.observe('click', function() {});
    });

    $$('input.validate-email').each(function (input) {
        input.writeAttribute('type', 'email');
    });

    $$('.form-list img[src*="calendar.gif"]').each(function (img) {
        img.up().insert({ 'top' : img });
    });

    if ( navigator.geolocation ) {
        //navigator.geolocation.getCurrentPosition(handler);
    }

    if ( $('my-reviews-table') ) {
        $('my-reviews-table').wrap('div', { 'class' : 'my-reviews-table-wrap' });
    }

    $$('.my-account .dashboard .box-title').each(function (elem) {
        elem.observe('click', function (e) {
            if ( e.target.hasClassName('box-title') ) {
                this.toggleClassName('collapsed').next().toggle();
            }
        }).next().hide();
    });

    var transformPref = Modernizr.prefixed('transform');

    function supportsTouchCallout () {
        var div = document.createElement('div'),
            supports = div.style['webkitTouchCallout'] !== undefined || div.style['touchCallout'] !== undefined;

        return supports
    }

    $$('input[name=qty], input[name*=super_group], input[name*=qty]').each(function (el) {
        var defaultValue = el.value;
        el.observe('focus', function () {
            if (this.value == defaultValue) this.value = '';
        });
        el.observe('blur', function () {
            if (this.value == "") this.value = defaultValue;
        });
    });

    if ( $('product-review-table') ) {
        $('product-review-table').wrap('div', {'class' : 'review-table-wrap'}).on('click', 'input[type="radio"]', function (e) {

            var $this = e.target;

            $this.up('tr').select('td').invoke('removeClassName', 'checked');
            if ( $this.up().hasClassName('value') ) {
                $this.up().addClassName('checked').previousSiblings().each(function (td) {
                    if (td.hasClassName('value')) td.addClassName('checked');
                });
            }

        });
    }

    function is_touch_device() {
      try {
        document.createEvent("TouchEvent");
        return true;
      } catch (e) {
        return false;
      }
    }

    var touch = is_touch_device();

    $$('select[multiple]').each(function (select) {
        var select_options = new Element('ol', {'class': 'select-multiple-options'}).wrap('div', { 'class' : 'select-multiple-options-wrap' }),
            selected;

        select.wrap('div', { 'class': 'select-multiple-wrap' });
        select.select('option').each(function(option) {
            select_options.down().insert({ bottom : new Element('li', { 'class' :  'select-option', 'data-option-value' : option.value }).update(option.text) });
        });

        select_options.insert({ top : new Element('div', { 'class' : 'select-heading' }).update('Choose options...').insert({ top : new Element('span', { 'class' : 'select-close' }).update('×') }) });

        var closeSelect = function() {
            select_options.setStyle({ 'visibility' : 'hidden' });
            selected = [];
            select.select('option').each(function (option) {
                if (option.selected) {
                    selected.push(option.text)
                }
            });

            if (selected.size() > 0) {
                select.previous().update('<span class="selected-counter"></span>' + selected.join(', ')).addClassName('filled');
                select.previous().select('span')[0].update(selected.size());
            } else {
                select.previous().update('Choose options...').removeClassName('filled');
            }
            document.stopObserving('click', closeSelect);
        }

        select_options.select('.select-close')[0].observe('click', closeSelect );

        select_options.on('click', '.select-option', function(e, elem) {
            var option = select.select('option[value=' + elem.readAttribute('data-option-value') + ']')[0];
            elem.toggleClassName('active');
            if (option.selected) {
                option.selected = false
            } else {
                option.selected = true;
            }
            if (typeof bundle !== 'undefined') bundle.changeSelection(select);
        });

        select.insert({ before : select_options });
        select.insert({
            before: new Element('div', {'class': 'select-multiple'}).update("Choose options...").observe('click', function(e) {
                    select.previous('.select-multiple-options-wrap').setStyle({ 'visibility' : 'visible' }).observe('click', function(e) {
                        e.stopPropagation();
                    });
                    setTimeout(function() {
                        document.observe('click', closeSelect)
                    }, 1);
                })
        });
        select.setStyle({ 'visibility' : 'hidden', 'position' : 'absolute' });
    });

    var supportsOrientationChange = "onorientationchange" in window,
    orientationEvent = supportsOrientationChange ? "orientationchange" : "resize";

    Event.observe(window, orientationEvent, function() {
        var orientation, page, transformValue = {};

        switch(window.orientation){
            case 0:
            orientation = "portrait";
            break;

            case -90:
            orientation = "landscape";
            break;

            case 90:
            orientation = "landscape";
            break;
        }

        if ( $('nav-container') ) {

            setTimeout(function () {
                $$("#nav-container ul").each(function(ul) {
                    ul.setStyle({'width' : document.body.offsetWidth + "px"});
                });

                page = Math.floor(Math.abs(sliderPosition/viewportWidth));
                sliderPosition = (sliderPosition + viewportWidth*page) - document.body.offsetWidth*page;
                viewportWidth = document.body.offsetWidth;

                if ( Modernizr.csstransforms3d ) {
                    transformValue[transformPref] = "translate3d(" + sliderPosition + "px, 0, 0)";
                } else if ( Modernizr.csstransforms ) {
                    transformValue[transformPref] = "translate(" + sliderPosition + "px, 0)";
                }
                $("nav-container").setStyle(transformValue);

                if ( upSellCarousel ) {
                    if (orientation === 'landscape') {
                        upSellCarousel.resize(3);
                    } else {
                        upSellCarousel.resize(2);
                    }
                }
            }, 400);

        }

    });

    //alert(Modernizr.prefixed('transform'));

    // Home Page Slider

    //alert(transformPref);
    var sliderPosition = 0,
        viewportWidth = document.body.offsetWidth,
        last,
        diff;

    $$("#nav-container ul").each(function(ul) { ul.style.width = document.body.offsetWidth + "px"; });

    $$("#nav a").each(function(sliderLink) {
        if (sliderLink.next(0) !== undefined) {
            sliderLink.clonedSubmenuList = sliderLink.next(0);

            sliderLink.observe('click', function(e) {

                e.preventDefault();
                var transformValue = {}

                //homeLink.hasClassName('disabled') ? homeLink.removeClassName('disabled') : '';

                if (last) {
                    diff = e.timeStamp - last
                }
                last = e.timeStamp;
                if (diff && diff < 200) {
                    return
                }
                if (!this.clonedSubmenuList.firstDescendant().hasClassName('subcategory-header')) {
                    var subcategoryHeader = new Element('li', {'class': 'subcategory-header'});
                    subcategoryHeader.insert({
                        top: new Element('button', {'class': 'previous-category'}).update("Back").wrap('div', {'class':'button-wrap'}),
                        bottom: this.innerHTML
                    });
                    this.clonedSubmenuList.insert({
                        top: subcategoryHeader
                    });
                    subcategoryHeader.insert({ after : new Element('li').update('<a href="' + sliderLink.href + '"><span>All Products</span></a>') });

                    this.clonedSubmenuList.firstDescendant().firstDescendant().observe('click', function(e) {
                        if (last) {
                            diff = e.timeStamp - last
                        }
                        last = e.timeStamp;
                        if (diff && diff < 200) {
                            return
                        }
                        if ( Modernizr.csstransforms3d ) {
                            transformValue[transformPref] = "translate3d(" + (document.body.offsetWidth + sliderPosition) + "px, 0, 0)";
                        } else if ( Modernizr.csstransforms ) {
                            transformValue[transformPref] = "translate(" + (document.body.offsetWidth + sliderPosition) + "px, 0)";
                        }
                        $("nav-container").setStyle(transformValue);
                        sliderPosition = sliderPosition + document.body.offsetWidth;
                        setTimeout(function() { $$("#nav-container > ul:last-child")[0].remove(); $("nav-container").setStyle({'height' : 'auto'})  }, 250)
                    });
                    new NoClickDelay(this.clonedSubmenuList);
                };

                $("nav-container").insert(this.clonedSubmenuList.setStyle({'width' : document.body.offsetWidth + 'px'}));
                $('nav-container').setStyle({'height' : this.clonedSubmenuList.getHeight() + 'px'});

                if ( Modernizr.csstransforms3d ) {

                    transformValue[transformPref] = "translate3d(" + (sliderPosition - document.body.offsetWidth) + "px, 0, 0)";

                } else if ( Modernizr.csstransforms ) {

                    transformValue[transformPref] = "translate(" + (sliderPosition - document.body.offsetWidth) + "px, 0)";

                }

                $("nav-container").setStyle(transformValue);

                sliderPosition = sliderPosition - document.body.offsetWidth;
            });
        };
    });

    function getSupportedProp(proparray){
        var root = document.documentElement;
        for ( var i = 0; i < proparray.length; i++ ) {
            if ( typeof root.style[proparray[i]] === "string") {
                return proparray[i];
            }
        }
    }

    function NoClickDelay(el) {
        if ( getSupportedProp(['OTransform']) ) {
            return
        }
        this.element = typeof el == 'object' ? el : document.getElementById(el);
        if( window.Touch ) this.element.addEventListener('touchstart', this, false);
    }

    NoClickDelay.prototype = {
        handleEvent: function(e) {
            switch(e.type) {
                case 'touchstart': this.onTouchStart(e); break;
                case 'touchmove': this.onTouchMove(e); break;
                case 'touchend': this.onTouchEnd(e); break;
            }
        },

        onTouchStart: function(e) {
            this.moved = false;

            this.theTarget = document.elementFromPoint(e.targetTouches[0].clientX, e.targetTouches[0].clientY);
            if(this.theTarget.nodeType == 3) this.theTarget = theTarget.parentNode;
            this.theTarget.className+= ' pressed';

            this.element.addEventListener('touchmove', this, false);
            this.element.addEventListener('touchend', this, false);
        },

        onTouchMove: function() {
            this.moved = true;
            this.theTarget.className = this.theTarget.className.replace(/ ?pressed/gi, '');
        },

        onTouchEnd: function(e) {
            e.preventDefault();

            this.element.removeEventListener('touchmove', this, false);
            this.element.removeEventListener('touchend', this, false);

            if( !this.moved && this.theTarget ) {
                this.theTarget.className = this.theTarget.className.replace(/ ?pressed/gi, '');
                var theEvent = document.createEvent('MouseEvents');
                theEvent.initEvent('click', true, true);
                this.theTarget.dispatchEvent(theEvent);
            }

            this.theTarget = undefined;
        }
    };

    if (document.getElementById('nav')) {
        new NoClickDelay(document.getElementById('nav'));
    }
	
	setWidthMenu();
	setItemWidth();
    
	if($('dLabel')){
		Event.observe('dLabel', 'click', function(){
			mgsjQuery('#currency-dropdown').hide();
			mgsjQuery('#language-dropdown').hide();
			mgsjQuery('#top-links').slideToggle();
			
		});
	}
	
	if($('languageLabel')){
		Event.observe('languageLabel', 'click', function(){
			mgsjQuery('#currency-dropdown').hide();
			mgsjQuery('#top-links').hide();
			mgsjQuery('#language-dropdown').slideToggle();
		});
	}
	
	if($('currencyLabel')){
		Event.observe('currencyLabel', 'click', function(){
			mgsjQuery('#language-dropdown').hide();
			mgsjQuery('#top-links').hide();
			mgsjQuery('#currency-dropdown').slideToggle();
		});
	}

	mgsjQuery('.top-cart .block-title').click(function(){
		mgsjQuery('.top-cart .cart-content').slideToggle();
	});

	mgsjQuery('#menu-button').click(function(){
		mgsjQuery('#currency-dropdown').hide();
		mgsjQuery('#language-dropdown').hide();
		mgsjQuery('#top-links').hide();
		setWidthMenu();
		mgsjQuery('#menu-collapse').addClass('active');
		mgsjQuery('#menu-collapse').show();
		mgsjQuery('#menu-collapse .content-bg').animate({width: 'toggle'});
		setTimeout(function(){ mgsjQuery('.wrapper').toggleClass('open-menu'); }, 250);
	});
	
	mgsjQuery(window).bind('orientationchange', function(e) {
		setItemWidth();
		setWidthMenu();
	});
	
	if($('filters-block')){
		mgsjQuery("#filters-block").detach().appendTo('#shopby');
	}
});

mgsjQuery(document).ready(function() {
	mgsjQuery('#opc-login .step-title').click(function(){
		mgsjQuery('#checkout-step-login').slideToggle();
	});
	mgsjQuery('#opc-billing .step-title').click(function(){
		mgsjQuery('#opc-billing #checkout-step-billing').slideToggle();
	});
	mgsjQuery('#opc-shipping .step-title').click(function(){
		mgsjQuery('#checkout-step-shipping').slideToggle();
	});
	mgsjQuery('#opc-shipping_method .step-title').click(function(){
		mgsjQuery('#checkout-step-shipping_method').slideToggle();
	});
	mgsjQuery('#opc-payment .step-title').click(function(){
		mgsjQuery('#checkout-step-payment').slideToggle();
	});
	mgsjQuery('#opc-review .step-title').click(function(){
		mgsjQuery('#checkout-step-review').slideToggle();
	});
});
function setWidthMenu(){
	var menuWidth = mgsjQuery('.wrapper').width();
	var menuHeight = mgsjQuery('.wrapper').height();
	
	currentHeight = mgsjQuery('#menu-collapse .content-bg').height();
	if(currentHeight>menuHeight){
		menuHeight = currentHeight;
	}
	
	mgsjQuery('#menu-collapse').width(menuWidth);
	mgsjQuery('#menu-collapse').height(menuHeight);
	mgsjQuery('#menu-collapse .content-bg').height(menuHeight);
	mgsjQuery('#menu-collapse .transparent-menu-bg').height(menuHeight);
}

function setItemWidth(){
	var mainWidth = mgsjQuery('.col-main').width();
	mgsjQuery('#filters-block .block-content').width(mainWidth-2);
	mainWidth = mainWidth - 12;
	itemWidth = mainWidth/2;
	mgsjQuery('.products-grid .item').width(itemWidth);
	
	
	var breadcrumbHeight = mgsjQuery(".breadcrumbs").height();
	if(breadcrumbHeight>22){
		mgsjQuery(".breadcrumbs").addClass('breadcrumbs-mini');
	}else{
		mgsjQuery(".breadcrumbs").removeClass('breadcrumbs-mini');
	}
}

function toogleFooterBLock(el){
	mgsjQuery('#'+el+' .block-title').toggleClass('title-active');
	mgsjQuery('#'+el+' .block-content').slideToggle();
}
// Price slider
function sliderAjax(url) {
	setLocation(url);
}

function toggleShopby(){
	mgsjQuery('#filters-block .block-content').slideToggle();
	mgsjQuery('#shopby .shop-by').toggleClass('label-active');
}

function toggleFilterItem(index){
	mgsjQuery('#filter-item-'+index+' dd').slideToggle();
	mgsjQuery('#filter-item-'+index+' dt').toggleClass('active');
}

function toggleProductTab(index){
	mgsjQuery('#tab-item-'+index+' .tab-content').slideToggle();
	mgsjQuery('#tab-item-'+index+' h3').toggleClass('active');
}

function openReviewTab(){
	toggleProductTab('reviews');
	mgsjQuery("html, body").animate({
        scrollTop: mgsjQuery('#tab-item-reviews').offset().top 
    }, 800);
}

function closeMenu(){
	mgsjQuery('#menu-collapse').removeClass('active');
	mgsjQuery('#menu-collapse').width(0);
	mgsjQuery('#menu-collapse').hide();
	mgsjQuery('#menu-collapse .content-bg').animate({width: 'toggle'});
	mgsjQuery('.wrapper').toggleClass('open-menu');
}

function toggleEl(el){
	mgsjQuery('#'+el).slideToggle();
}

// init gmap
function initGmap(address, html, image){
	mgsjQuery.ajax({
		type: "GET",
		dataType: "json",
		url: "http://maps.googleapis.com/maps/api/geocode/json",
		data: {'address': address,'sensor':false},
		success: function(data){
			if(data.results.length){
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
				map: map ,
				icon: image
				});
		
		
			  google.maps.event.addListener(marker, 'click', (function(marker, i) {
				return function() {
				  infowindow.setContent(locations[i][0]);
				  infowindow.open(map, marker);
				}
			  })(marker, i));
			}
			}
		}
	});
}
