/*
 * UTurn 
 * v0.1
 * 
 * Tomas Reimers
 * 
 * The JS needed to support the form.
 */

// can't do anything until the DOM is ready, also encloses my code
$(document).ready(function (){
    if (wgCanonicalSpecialPageName == 'UTurn'){
        // rather than submit the page, we will do an AJAX request
        $('#uturn-form').bind('submit', function (ev){

            var submitField = $(this).find('#uturn-date')[0];

            // form can't be empty
            if (submitField.value == ''){
                $(submitField).css('border-color', '#000000');
                return;
            }

            var timestamp;
            var validtext = /^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4}) ([0-9]{1,2})\:([0-9]{1,2})\:([0-9]{1,2})$/;

            // date must be in valid format
            if (validtext.test(submitField.value)){

                $(submitField).css('border-color', '#000000');

                var dateParts = submitField.value.match(validtext);
                timestamp = Date.UTC(
                    dateParts[3], 
                    dateParts[1] - 1, 
                    dateParts[2], 
                    dateParts[4], 
                    dateParts[5], 
                    dateParts[6],
                    0
                ) / 1000;

                // can't do future time
                if (timestamp > ((new Date()).getTime() / 1000)){
                    $(submitField).css('border-color', '#FF0000');
                    return;
                }
            }
            // date was in invalid format
            else {
                $(submitField).css('border-color', '#FF0000');
                return false;
            }

            // If did not return before this, then request is valid

            $('#uturn-status').html('UTurning...').css('color', '#999999');
            // easy way to select both the button and the textfield
            $('#uturn-form input').attr('disabled', 'disabled');

            $.ajax({
                url: '',
                data: {
                    action: 'submit',
                    t: timestamp,
                    editToken: mediaWiki.user.tokens.values.editToken
                },
                type: 'POST',
                complete: function (){
                    $('#uturn-status').html('');
                    $('#uturn-form input').removeAttr('disabled');
                }
            });

            // Done, make sure we block the form from submitting anyway (because already ajax submitted) 
            return false;
        });
    }
});