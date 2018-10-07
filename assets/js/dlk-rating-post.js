( function( $ ) {
    $( document ).ready(function() {

        $("#toggleRating").on( 'click', function( e ){
            e.preventDefault();

            $(this).toggleClass('active');

            var $text = $(this).children('.text');

            $("#entryRating").toggleClass('active');

            if( $("#entryRating").hasClass('active') ) {
                $text.html( dlk_object.text.close_rating );
            } else {
                $text.html( dlk_object.text.rate_it );
            }

        });

        $("#submitRating").on( 'click', function(){

            var $this = $( this ),
                $text = $("#toggleRating").children('.text'),
                val = $("input[name=ratingValue]:checked").val();

            $("#ratingErrors").html('');

            if( ! val ) {

                $("#ratingErrors").html( dlk_object.text.choose_rate );

            } else {

                var rate_id = $this.attr("data-rate");

                if( rate_id == 0 ) {
                    return;
                }

                $this.html( dlk_object.text.submitting );

                $.ajax({
                    url: dlk_object.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: { action: 'submit_rating', _wpnonce: dlk_object.nonce, rating: val, post_id: rate_id },
                    success: function( resp ) {
                        if( resp.success ) {

                            $text.html( dlk_object.text.thank_you );

                            $("#entryRating").removeClass( 'active' );

                        } else {

                            $("#ratingErrors").html( resp.message );

                            $this.html( dlk_object.text.submit );

                        }
                    }
                });
            }
        });
    });
})(jQuery);