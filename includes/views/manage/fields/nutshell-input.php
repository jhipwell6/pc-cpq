<div class="form-group row" id="nutshell-input">
	<label for="nutshell_id" class="col-sm-2 col-form-label">Nutshell ID</label>
	<div class="col-sm-10">
		<?php if ( $Lead->get_nutshell_id() && $Lead->get_quote_number() && PC_CPQ()->Settings()->is_nutshell_enabled() ) : ?>
		<div class="input-group">
			<input type="text" id="nutshell_id" class="form-control form-control-border" value="<?php echo $Lead->get_nutshell_id(); ?>" readonly>
		</div>
		<?php ; elseif ( ! PC_CPQ()->Settings()->is_nutshell_enabled() ) : ?>
		<span class="d-inline-block col-form-label text-muted">Nutshell is not active in your workspace</span>
		<?php ; elseif ( ! PC_CPQ()->Settings()->has_nutshell_credentials() ) : ?>
		<span class="d-inline-block col-form-label text-muted">Add your Nutshell credentials in Integrations before sending this lead</span>
		<?php ; elseif ( $Lead->get_id() == 0 ) : ?>
		<span class="d-inline-block col-form-label text-muted">The lead must be saved first</span>
		<?php ; else : ?>
		<button type="button" class="btn btn-secondary btn-sm js-send-to-nutshell">Send to Nutshell</button>
		<?php endif; ?>
	</div>
</div>
