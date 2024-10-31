jQuery(document).ready(function($) {    
    var $toggleButton = $('#toggle_api_token');
    var $apiTokenInput = $('#rocket_validator_api_token');

    $toggleButton.on('click', function() {
        var isPassword = $apiTokenInput.attr('type') === 'password';
        $apiTokenInput.attr('type', isPassword ? 'text' : 'password');
        $toggleButton.find('.screen-reader-text').text(isPassword ? 'Hide API Token' : 'Show API Token');
        $toggleButton.find('[aria-hidden="true"]').text(isPassword ? 'Hide' : 'Show');
        $apiTokenInput.focus();
    });
});
