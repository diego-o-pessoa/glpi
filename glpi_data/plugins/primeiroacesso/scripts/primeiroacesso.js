$(document).ready(function() {
    // Only apply if we are on user.form.php or preference.php
    if (window.location.pathname.indexOf('user.form.php') !== -1 || window.location.pathname.indexOf('preference.php') !== -1) {
        
        // Bloqueia a Localização e a Entidade Padrão
        var locField = $('select[name=\"locations_id\"]');
        var entField = $('select[name=\"entities_id\"]');
        
        if (locField.length) {
            locField.prop('disabled', true);
            // Se estiver usando o select2 do GLPI
            if (locField.hasClass('select2-hidden-accessible')) {
                locField.on('select2:opening', function (e) {
                    e.preventDefault();
                });
            }
            
            $('<input>').attr({
                type: 'hidden',
                name: 'locations_id',
                value: locField.val()
            }).appendTo(locField.parent());
        }
        
        if (entField.length) {
            entField.prop('disabled', true);
            // Se estiver usando o select2 do GLPI
            if (entField.hasClass('select2-hidden-accessible')) {
                entField.on('select2:opening', function (e) {
                    e.preventDefault();
                });
            }
            
            $('<input>').attr({
                type: 'hidden',
                name: 'entities_id',
                value: entField.val()
            }).appendTo(entField.parent());
        }
    }
});
