



jQuery(document).ready(function($) {

    // Buttons mit IDs 'b1', 'b2' und 'b3' verarbeiten
    $('#b1').on('click', function() {
        $.ajax({
            url: myButtonAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'button1_action', // WP Ajax Action
                nonce: myButtonAjax.nonce   // Nonce zur Sicherheit
            },
            success: function(response) {
                if (response.success) {
                    $('#status').html(response.data);
                    //alert(response.data); // Zeige die Antwort der PHP-Funktion
                } else {
                    $('#status').html(response.data);('Es gab ein Problem.');
                }
            }
        });
    });
    $('#b2').on('click', function() {
        $.ajax({
            url: myButtonAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'button2_action', // WP Ajax Action
                nonce: myButtonAjax.nonce   // Nonce zur Sicherheit
            },
            success: function(response) {
                if (response.success) {
                    $('#status').html(response.data);
                    //alert(response.data); // Zeige die Antwort der PHP-Funktion
                } else {
                    $('#status').html(response.data);('Es gab ein Problem.');
                }
            }
        });
    });
    $('#b3').on('click', function() {
        $.ajax({
            url: myButtonAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'button3_action', // WP Ajax Action
                nonce: myButtonAjax.nonce   // Nonce zur Sicherheit
            },
            success: function(response) {
                if (response.success) {
                   document.getElementById("checkbox").checked = false;
                    $('#status').html(response.data);
                } else {
                    $('#status').html(response.data);('Es gab ein Problem.');
                }
            }
        });
    });
    $('#b4').on('click', function() {
        clear_status()
        $.ajax({
            url: myButtonAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'button4_action', // WP Ajax Action
                nonce: myButtonAjax.nonce   // Nonce zur Sicherheit
            },
            success: function(response) {
                if (response.success) {
                    $('#status_del').html(response.data.p1);
                    $('#status').html(response.data.p2);
                    //alert(response.data); // Zeige die Antwort der PHP-Funktion
                } else {
                    $('#status_del').html(response.data);
                }
            },
            error: function () {
                console.error("Fehler beim Abrufen des Fortschritts.");
            },
            complete: function () {
                //page_reload();
            }
        });
    });
    $('#b5').on('click', function() {
        clear_status()
        let progressInterval;
        $('#progress').html('Die Einzelveranstaltungen werden bearbeitet');
        jQuery('#sanduhr').show();
        today = get_yesterday_date()
        alert('Achtung!!\nalle vergangenen Einzelveranstaltungen bis zum\n' + today + '\nwerden in den Papierkorb verschoben')
        $.ajax({
            url: myButtonAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'button5_action', // WP Ajax Action
                nonce: myButtonAjax.nonce   // Nonce zur Sicherheit
            },
            success: function(response) { 
                const actionId = 2;
                clear_status()
                if (response.success) {
                    $(`#${"progressBar"}`).hide();
                    $('#status_del').html(response.data.p1);
                    $('#status').html(response.data.p2);
                    jQuery('#sanduhr').hide();
                    //alert(response.data); // Zeige die Antwort der PHP-Funktion
                } else {
                    $('#status_del').html(response.data);
                }

            },
            error: function () {
                console.error("Fehler beim Abrufen des Fortschritts.");
            },
            complete: function () {
                //page_reload();
            }

        });
         
    });

    $('#b6').on('click', function() {
        
        clear_status();
        $('#progress').html('Die Einzelveranstaltungen werden bearbeitet');
        jQuery('#sanduhr').show();

        today = get_yesterday_date()
        alert('Achtung!!\nalle vergangenen Einzelveranstaltungen bis zum\n' + today + '\nwerden im Events-Papierkorb gelöscht')
        $.ajax({
            url: myButtonAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'button6_action', // WP Ajax Action
                nonce: myButtonAjax.nonce   // Nonce zur Sicherheit
            },
            success: function(response) {
                const actionId = 1;
                clear_status()
                if (response.success) {
                    //$('#status_del').html(response.data); // Meldung vom Server
                    $(`#${"progressBar"}`).hide();
                    $('#status_del').html(response.data.p1);
                    $('#status').html(response.data.p2);
                    jQuery('#sanduhr').hide();
                    //alert(response.data); // Zeige die Antwort der PHP-Funktion
                } else {
                    $('#status_del').html(response.data);
                }
            },
            error: function () {
                console.error("Fehler beim Abrufen des Fortschritts.");
            },
            complete: function () {
                //page_reload();
            }
        });
 
    });


    //-------------------------------------
    // helper


    function page_reload() {
        console.log("AJAX-Anfrage abgeschlossen.");
        setTimeout(() => {
            location.reload();
        }, 5000);
       
    }


        function clear_status() {
        $('#status').html('');
        $('#status_del').html('');
        $('#progress').html('');
       
    }

    function get_yesterday_date() {
        var today = new Date();
        today.setDate(today.getDate() -1);
        var dd = String(today.getDate()).padStart(2, '0');
        var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
        var yyyy = today.getFullYear();
        var today = dd + '/' + mm + '/' + yyyy;
        return today
    }
});

