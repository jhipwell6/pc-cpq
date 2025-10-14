<script id="part" type="template">
	<fieldset class="form-group mb-2">
		<legend class="d-flex w-100 p-2 bg-white">{{fileName}}<span class="ml-auto"></span>{{buttons}}</legend>
		<div class="row pb-2 collapse{{show}}" id="collapse_{{ID}}" data-part="{{ID}}">
			{{fields}}
		</div>
	</fieldset>
</script>

<script id="part-field" type="template">
	<div class="col-{{col}}">
		<label{{labelClass}}>{{label}} *</label>
		{{input}}
	</div>
</script>

<script id="collapse-button" type="template">
	<button class="btn btn-sm button gform_button btn-pulse" type="button" data-toggle="collapse" data-target="#collapse_{{ID}}">Configure</button>
</script>

<script id="use-same-process" type="template">
	<div class="form-check">
		<input type="checkbox" class="form-check-input" data-action="use-same-process" value="" id="use-same-process">
		<label class="form-check-label" for="use-same-process"> Use the same process for all parts</label>
	</div>
</script>

<script id="copy-button" type="template">
	<button class="btn btn-sm btn-link {{displayClass}}" type="button" data-action="copy" data-part="{{ID}}">copy processes</button>
</script>

<script id="paste-button" type="template">
	<button class="btn btn-sm btn-link {{displayClass}}" type="button" data-action="paste" data-part="{{ID}}">paste processes</button>
</script>

<script id="copy-message" type="template">
	<span class="btn btn-sm btn-link btn-msg {{displayClass}}" data-part="{{ID}}">copied</span>
</script>

<script id="paste-message" type="template">
	<span class="btn btn-sm btn-link btn-msg {{displayClass}}" data-part="{{ID}}">pasted</span>
</script>

<script id="text-input" type="template">
	<input {{atts}} autocomplete="off" />
</script>

<script id="select-input" type="template">
	<select {{atts}}>
		{{options}}
	</select>
</script>

<script id="select-input-option" type="template">
	<option value="{{value}}"{{selected}}>{{label}}</option>
</script>

<script id="action-buttons" type="template">
	<div class="col">
		<div class="btn-group" role="group">
			{{buttons}}
		</div>
	</div>
</script>

<script id="action-button" type="template">
	<button {{atts}}>{{label}}</button>
</script>