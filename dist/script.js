jQuery(document).ready(function($){
        
    jQuery('#free-image-search').submit(function(event) {
        event.preventDefault();
                
        //loading spinner
        jQuery('#found-images').html('<img src="images/wpspin_light.gif" class="fi-spinning-loader" />');

        var img_url = "http://freeimages.pictures/api/user/"+
            jQuery('#free-image-search input[name=api-key]').val()+
            "/?keyword="+jQuery('#free-image-search input[type=text]').val()+
            "&sources="+jQuery('#free-image-search input[name=enabled-sources]').val()+
            "&format=jsonp";
        
        var request = jQuery.ajax({
                type: 'GET',
                url: img_url,
                async: false,
                jsonpCallback: 'jsonCallback',
                contentType: "application/json",
                dataType: 'jsonp',
                success: function(json) {
                    jQuery('#free-image-search .fi-results-number').text(json.count+' images found');
                    var found_images_div = jQuery('#found-images');
                    jQuery.each(json.sources, function(i,source) {
                        jQuery.each(source.result, function(j,res) {
                            var data_src = source.source_name == 'flickr' ? res.preview_url : res.url;
                            found_images_div.append('<div class="fi-found-image" data-title="'+res.title+'" data-src="'+data_src+'" data-preview="'+res.preview_url+'" style="display: inline-block; margin: 3px; width: 180px; height: 150px; background:url('+res.thumb_url+') no-repeat center center; cursor: pointer;"></div>');
                        });
                    });
                },
                error: function(e) {
                    //console.log(e.message);
                },
                complete: function(e) {
                    jQuery('img.fi-spinning-loader').remove();
                }
        });
    
    });
    
    var loadingImage;
    jQuery(document)
        .on('mouseenter', '#found-images .fi-found-image', function(event) {
            loadingImage = jQuery('<img class="fi-image-preview" style="visibility:hidden;z-index:1000000;position:absolute; max-height: '+$( window ).height()+'px;" src="'+jQuery(this).data('preview')+'"/>');
            jQuery('body').append(loadingImage);
            
            loadingImage.load(function() {
                foundImageMouseMove(event);
                loadingImage.css({'visibility':'visible'});
            });
        })
        .on('mousemove', '#found-images .fi-found-image', function(event) {
            loadingImage.load(function() {
                foundImageMouseMove(event);
                loadingImage.css({'visibility':'visible'});
            });
            foundImageMouseMove(event);
        })
        .on('mouseleave', '#found-images .fi-found-image', function() {
            jQuery('img.fi-image-preview').remove();
        });
    function foundImageMouseMove(event) {
        var image = jQuery('img.fi-image-preview');
        var left = (event.pageX > $( window ).width()-event.pageX) ? event.pageX-image.width()-20 : event.pageX+20;
        var top = (event.pageY + image.height() > $(window).scrollTop()+$( window ).height()) ? $(window).scrollTop()+$( window ).height()-image.height() : event.pageY;
        jQuery('img.fi-image-preview').css({left: left, top: top});
    }

    
    // AJAX
    jQuery(document).on('click', '#found-images .fi-found-image', function() {
    
        //remove preview
        jQuery('img.fi-image-preview').remove();
        
        jQuery('#found-images').html('<img src="images/wpspin_light.gif" class="fi-spinning-loader" />');
        
        var data = {
            action: 'save_image',
            img_title: jQuery(this).data('title'),
            img_src: jQuery(this).data('src'),
            post_id: ajax_object.post_id
        };
        jQuery.post(ajax_object.ajax_url, data, function(response) {
            //alert('Got this from the server: ' + response);
            //refresh media library
            wp.media.editor.remove('content');
            wp.media.editor.add('content');
            
            var img = $(response);

            var a = $('<a />');
            a.attr('href', img.attr('src'));

            var p = $('<p />');

            a.append(img);
            p.append(a);
            send_to_editor(p.html());
    
        })
        .always(function() {
            //reset
            jQuery('#free-image-search').val('');
            jQuery('img.fi-spinning-loader').remove();
            jQuery('#free-image-search .fi-results-number').text('');
            jQuery('#free-image-search input[type=text]').val('');
        });
	});
    
    $(window).bind('tb_unload', function() {
        jQuery('img.fi-image-preview').remove();
    });
});