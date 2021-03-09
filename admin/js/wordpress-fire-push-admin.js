(function( $ ) {

	// USE STRICT
    "use strict";

    var ticket = {

        init : function (wordpress_fire_push_settings) {

        	this.settings = wordpress_fire_push_settings;
        	this.savedReplyButtonCreated = false;

            ticket.getSavedReplies();
            ticket.loadSavedReply();
            ticket.showAttachmentFields();
            ticket.ticketNote();
        },
        showAttachmentFields : function() {

            // $('form#post')[0].encoding = 'multipart/form-data';

            // var reply = $('#wp-replycontent-wrap');
            // var html = '<p class="wordpress-fire-push-attachments">' + 
            //               '<label for="author">Attachments</label>' + 
            //               '<input name="fire-push-attachments[]" type="file" accept="image/*" multiple>' +
            //             '</p>';

            // reply.prepend(html);

        },
       	getSavedReplies : function() {

            var that = this;
       		var commentButton = $('#commentsdiv a.button');
       		var replySubmit = $('#replysubmit');
       		var spinner = $('#replysubmit .waiting spinner');

       		commentButton.on('click', function(e) {

       			var content = $('.wp-editor-container #content').text();
       			content = content.replace(/(<([^>]+)>)/ig,"");

       			if(that.savedReplyButtonCreated == false) {
                	that.savedReplyButtonCreated = true;

		            $.ajax({
	                    type : 'post',
	                    dataType : 'json',
	                    url : that.settings.ajax_url,
	                    data : {
	                        action : 'search_saved_replies',
	                        content: content
	                    },
	                    success : function( response ) {
	                        if(response.status == "true") {
	                        	
	                        	var sel = $('<select id="select-saved-reply" class="alignright">').appendTo(replySubmit);

	                        	sel.append($("<option>").text('Select a saved Reply').attr('value', ''));

	                        	if(response.suggessted_replies.length > 0) {
	                    			var optgroup = $("<optgroup>").attr('label', 'Suggested Replies').appendTo(sel);

									$(response.suggessted_replies).each(function() {
										optgroup.append($("<option>").attr('value', this.ID).text(this.post_title));
									});
	                        	}

	                        	if(response.all_replies.length > 0) {
	                    			var optgroup = $("<optgroup>").attr('label', 'All Replies').appendTo(sel);
	                    			
									$(response.all_replies).each(function() {
										optgroup.append($("<option>").attr('value', this.ID).text(this.post_title));
									});
	                        	}
	                        }
                    	}
                 	});
             	} else {
             		$('#select-saved-reply').trigger('change');
             	}
       		});
       	},
       	loadSavedReply : function() {

            var that = this;
       		var replySubmit = $('#replysubmit');
       		var spinner = $('#replysubmit .waiting spinner');

       		replySubmit.on('change', '#select-saved-reply', function(e) {

       			var option = $(this);
       			var replyID = option.val();
       			console.log(replyID);

       			if(replyID.length > 0) {

       				spinner.css('visibility', 'visible');
                    $.ajax({
	                    type : 'post',
	                    dataType : 'json',
	                    url : that.settings.ajax_url,
	                    data : {
	                        action : 'get_saved_reply',
	                        id : replyID,
	                    },
	                    success : function( response ) {
	                    	spinner.css('visibility', 'hidden');
	                    	if(response.status == "true") {
	                    		$('#replycontent').val(response.reply.post_content);
	                    	}
                    	}
                	});
       			}

       		});
       	},
        ticketNote : function() {

            var that = this;
            var ticketNoteButton = $('#add_ticket_note_button');

            ticketNoteButton.on('click', function(e) {
                e.preventDefault();

                var ticketNote = $('#ticket_note').val();
                if(ticketNote == "") {
                    return false;
                }

                var ticketID = $('#ticket_note_ticket_id').val();

                $.ajax({
                    type : 'post',
                    dataType : 'json',
                    url : that.settings.ajax_url,
                    data : {
                        action : 'create_ticket_note',
                        id : ticketID,
                        note : ticketNote
                    },
                    success : function( response ) {
                        $('.ticket_notes').append(response);
                        $('#ticket_note').val('');
                    }
                });

            });

            $('.ticket_notes').on('click', '.delete_note', function(e) {
                e.preventDefault();

                var noteID = $(this).data('id');
 
                var ticketID = $('#ticket_note_ticket_id').val();

                $.ajax({
                    type : 'post',
                    dataType : 'json',
                    url : that.settings.ajax_url,
                    data : {
                        action : 'delete_ticket_note',
                        id : ticketID,
                        noteID : noteID
                    },
                    success : function( response ) {
                        $('.ticket_notes #note-' + noteID).fadeOut();
                    }
                });

            });
        }
    };

    $(document).ready(function() {
    	if( $('body.post-type-ticket').length > 0) {
    		ticket.init(wordpress_fire_push_settings);
    	}
		
		
		
jQuery( ".regular-text" ).keyup(function() {
  
	var Title = jQuery( "#customTitle" ).val();
	var customBody = jQuery( "#customBody" ).val();
	var customURL = jQuery( "#customURL" ).val();
	var editIcon = jQuery("#wordpress_fire_push_options-customIcon input").val();
	
	
	
	var siteurl = jQuery("#siteurl").text();
	jQuery( ".chromeview" ).html('<div class="leftchrm"><div class="chromeicon"><img src="'+editIcon+'"  ></div></div><div class="rightchrm"> <p class="chrtitle">'+Title+'</p><p class="chrbody">'+customBody+'</p><p class="chrbody">Google Chrome.</p><p class="chrurl">'+siteurl+'</p></div>  ');


	jQuery( ".firefoxview" ).html('<p class="firetitle">'+Title+'</p><div class="leftchrm"><div class="fireicon"><img src="'+editIcon+'"  ></div></div><div class="rightchrm"> <p class="firebody">'+customBody+'</p><p class="fireurl">'+siteurl+'</p></div>  ');

	jQuery( ".Androidview" ).html('<div class="leftchrm"><div class="chromeicon"><img src="'+editIcon+'"  ></div></div><div class="rightchrm"> <p class="firetitle">'+Title+'</p><p class="andbody">'+customBody+'</p><p class="firefoxurl">'+siteurl+'</p></div>');
	
	jQuery( ".chromemac" ).html('<div class="leftios"><div class="chrmicon"><img src="'+siteurl+'/wp-content/plugins/gm-push-notification/public/images/chrome.png"  ></div></div><div class="rightios"> <p class="iostitle">'+Title+'</p><p class="iosbody">'+customBody+'</p></div><div class="leftnios"><div class="chrmioscon"><img src="'+editIcon+'"  ></div> </div>');
   
});


function loadviews(){

	var Title = jQuery( "#customTitle" ).val();
	var customBody = jQuery( "#customBody" ).val();
	var customURL = jQuery( "#customURL" ).val();
	var editIcon = jQuery("#wordpress_fire_push_options-customIcon input").val();
	
	
	var siteurl = jQuery("#siteurl").text();
	jQuery( ".chromeview" ).html('<div class="leftchrm"><div class="chromeicon"><img src="'+editIcon+'"  ></div></div><div class="rightchrm"> <p class="chrtitle">'+Title+'</p><p class="chrbody">'+customBody+'</p><p class="chrbody">Google Chrome.</p><p class="chrurl">'+siteurl+'</p></div>  ');


	jQuery( ".firefoxview" ).html('<p class="firetitle">'+Title+'</p><div class="leftchrm"><div class="fireicon"><img src="'+editIcon+'"  ></div></div><div class="rightchrm"> <p class="firebody">'+customBody+'</p><p class="fireurl">'+siteurl+'</p></div>  ');

	jQuery( ".Androidview" ).html('<div class="leftchrm"><div class="chromeicon"><img src="'+editIcon+'"  ></div></div><div class="rightchrm"> <p class="firetitle">'+Title+'</p><p class="andbody">'+customBody+'</p><p class="firefoxurl">'+siteurl+'</p></div>  ');
	
	jQuery( ".chromemac" ).html('<div class="leftios"><div class="chrmicon"><img src="'+siteurl+'/wp-content/plugins/gm-push-notification/public/images/chrome.png"  ></div></div><div class="rightios"> <p class="iostitle">'+Title+'</p><p class="iosbody">'+customBody+'</p></div><div class="leftnios"><div class="chrmioscon"><img src="'+editIcon+'"  ></div></div> ');
	
	
}
loadviews();
		
		
	});

})( jQuery );
