<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		$target = $this->getvar("target");
		if (!$target) {
			$target = $this->path;
		}

		$jail = ar::acquire('settings.jail');

		if (!$jail) {
			$jail = '/';
		}
		if (substr($jail, -1) != "/") {
			$jail .= "/";
		}

		if ($jail) {
			$target = preg_replace("|^$jail|", '/', $target);
		}

		$crumbs = '';
		$path = '';
		$parents = $this->parents($this->path, 'system.get.name.phtml', '', $jail);
		$parentpaths = $this->parents($this->path, 'system.get.path.phtml', '', $jail);
		$path = array_pop($parentpaths);

		if ( strpos( $path, $jail ) === 0 ) {
			$path = substr( $path, strlen($jail)-1 );
		}

		array_pop($parents); //remove current item from the list.
		array_shift( $parents ); // remove site jail from the list.
		foreach ($parents as $name) {
			$crumbs .= "/ $name";
		}

		$oldcrumbs = $crumbs;
		if (strlen($crumbs) > 18) {
			$crumbs = mb_substr($crumbs, 0, 12, "utf-8") . "..." . mb_substr($crumbs, strlen($crumbs)-5, strlen($crumbs), "utf-8");
			if (strlen($crumbs) >= strlen($oldcrumbs)) {
				$crumbs = $oldcrumbs;
			}
		}
		$crumbs = htmlspecialchars( $crumbs . "/ " . $nlsdata->name );
		$oldcrumbs = htmlspecialchars( $oldcrumbs . "/ " . $nlsdata->name );

		if( !$ARCurrent->arTypeTree ) {
			$this->call('typetree.ini');
		}
		$icons = $ARCurrent->arTypeIcons;
		$names = $ARCurrent->arTypeNames;

		$icon = $ARCurrent->arTypeIcons[$this->type]['medium'] ? $ARCurrent->arTypeIcons[$this->type]['medium'] : $this->call("system.get.icon.php", array('size' => 'medium'));

		$iconalt = $this->type;
		if ( $this->implements("pshortcut") ) {
			$overlay_icon = $icon;
			$overlay_alt = $this->type;
			if ( $ARCurrent->arTypeIcons[$this->vtype]['medium'] ) {
				$icon = $ARCurrent->arTypeIcons[$this->vtype]['medium'];
			} else {
				$icon = current($this->get($this->data->path, "system.get.icon.php", array('size' => 'medium')));
			}
			$iconalt = $this->vtype;
		}

?>
<script type="text/javascript">
	function callback(path) {
		var jail = "<?php echo $jail; ?>";
		if (path.indexOf(jail) == 0) {
			path = path.substring(jail.length-1, path.length);
		} 
		document.getElementById("relativetarget").value = path;
		updateTarget();
	}

	function updateTarget() {
		var jail = "<?php echo $jail; ?>";
		document.getElementById("target").value = (jail + document.getElementById("relativetarget").value).replace("//", "/");
	}

</script>
<fieldset id="data" class="browse">
		<?php
			echo '<img src="' . $icon . '" alt="' . htmlspecialchars($iconalt) . '" title="' . htmlspecialchars($iconalt) . '" class="typeicon">';
		
		if ( $overlay_icon ) {
			echo '<img src="' . $overlay_icon . '" alt="' . htmlspecialchars($overlay_alt) . '" title="' . htmlspecialchars($overlay_alt) . '" class="overlay_typeicon">';
		}
		echo '<div class="name">' . $nlsdata->name . ' ';
		echo '( <span class="crumbs" title="' . $oldcrumbs . '">' . $crumbs . '</span> )';
		echo '</div>';
		echo '<div class="path">' . $path . '</div>';
		?>
		<div class="browse_wrapper">
			<div class="field">
				<label for="target" class="required"><?php echo $ARnls["target"]; ?></label>
				<input id="relativetarget" type="text" name="relativetarget" value="<?php echo $target; ?>" class="inputline wgWizAutoFocus" onchange="updateTarget();" onkeyup="updateTarget();">
				<input type="hidden" id="target" name="target" value="<?php echo htmlentities(str_replace("//", "/", $jail . $target)) ?>">
				<input class="button" type="button" value="<?php echo $ARnls['browse']; ?>" title="<?php echo $ARnls['browse']; ?>" onclick='window.open("<?php echo $this->make_ariadne_url($jail); ?>" + document.getElementById("relativetarget").value + "dialog.browse.php", "browse", "height=480,width=750"); return false;'>
				<div class="clear"></div>
			</div>
		</div>
<?php 		if ($this->CheckSilent("layout")) { ?>
		<div class="field checkbox">
			<input id="override_typetree" type="checkbox" name="override_typetree" value="1">
			<label for="override_typetree"><?php echo $ARnls['ariadne:override_typetree']; ?></label>
		</div>
<?php		} ?>
</fieldset>
<?php	} 
?>