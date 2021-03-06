<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("read") && $this->CheckConfig()) {
?>
<fieldset id="data">
	<legend><?php echo $ARnls["address"]; ?></legend>
	<div class="field">
		<label for="address" class="required"><?php echo $ARnls["address"]; ?></label>
		<input id="address" type="text" name="address" value="<?php $this->showdata("address", "none"); ?>" class="inputline wgWizAutoFocus">
	</div>
<?php
	/* FIXME: We should start using ISO standard addresses 
	<div class="field">
		<label for="address2" class="required"><?php echo $ARnls["address2"]; ?></label>
		<input id="address2" type="text" name="address2" value="<?php $this->showdata("address2", "none"); ?>" class="inputline">
	</div>
	*/
?>
	<div class="field">
		<label for="zipcode" class="required"><?php echo $ARnls["zipcode"]; ?></label>
		<input id="zipcode" type="text" name="zipcode" value="<?php $this->showdata("zipcode", "none"); ?>" class="inputline">
	</div>
	<div class="field">
		<label for="city" class="required"><?php echo $ARnls["city"]; ?></label>
		<input id="city" type="text" name="city" value="<?php $this->showdata("city", "none"); ?>" class="inputline">
	</div>
	<div class="field">
		<label for="country" class="required"><?php echo $ARnls["country"]; ?></label>
		<select id="country" name="country">
			<option value=''><?php echo $ARnls["none"]; ?></option>
			<?php
				// FIXME: country names must be added for all available languages, currently ordered by english value
				$query="object.parent='/system/addressbook/countries/' and name.nls='en' order by name.value";
				$this->find("/system/addressbook/countries/", $query, "show.option.value.phtml",Array( "selected" => $this->getdata("country","none")),0);
			?>
		</select>
	</div>
	<div class="field">
		<label for="state" class="required"><?php echo $ARnls["state"]; ?></label>
		<input id="state" type="text" name="state" value="<?php $this->showdata("state", "none"); ?>" class="inputline">
	</div>
</fieldset>
<?php
	}
?>