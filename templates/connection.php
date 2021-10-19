<?php

global $FapiPlugin;

echo FapiMemberTools::heading();
?>
<div class="page">
    <h3>Propojený účet FAPI</h3>
	<?php echo FapiMemberTools::showErrors(); ?>
	<?php echo FapiMemberTools::formStart('api_credentials_submit') ?>
    <div class="row">
        <label for="fapiMemberApiEmail">Uživatelské jméno (e-mail)</label>
        <input type="text" name="fapiMemberApiEmail" id="fapiMemberApiEmail" placeholder="me@example.com"
               value="<?php echo get_option(FapiMemberPlugin::OPTION_KEY_API_USER, '') ?>">
    </div>
    <div class="row">
        <label for="fapiMemberApiKey">API klíč</label>
        <input type="text" name="fapiMemberApiKey" id="fapiMemberApiKey" placeholder=""
               value="<?php echo get_option(FapiMemberPlugin::OPTION_KEY_API_KEY, '') ?>">
    </div>
    <div class="row controls">
        <input type="submit" class="primary" name="" id="" value="Propojit s FAPI">
    </div>
    </form>
    <p>
        Stav propojení:
		<?php echo ($FapiPlugin->recheckApiCredentials()) ? '<span class="ok">propojeno</span>' : '<span class="ng">nepropojeno</span>' ?>
    </p>
</div>
<?php echo FapiMemberTools::help() ?>
</div>
