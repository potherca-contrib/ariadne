<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		include($this->store->get_config("code")."widgets/wizard/code.php");

		$wgWizFlow = array(
			array(
				"current" => $this->getdata("wgWizCurrent","none"),
				"template" => "dialog.svn.tree.diff.form.php",
				"cancel" => "window.close.js",
				"save" => "dialog.svn.tree.revert.save.php"
			)
		);

		$wgWizAction = $this->getdata("wgWizAction");
		
		if( $wgWizAction == "save" ) {
			$wgWizStyleSheets = array( $AR->dir->styles."svn.css" );
			
			$wgWizButtons = array(
				"cancel" => array(
					"value" => $ARnls["ok"]
				)
			);
		} else {
			$wgWizButtons = array(
				"cancel" => array(
					"value" => $ARnls["cancel"]
				),
				"save" => array(
					"value" => $ARnls["ariadne:svn:revert"]
				),
			);
		}
	
		$wgWizTitle=$ARnls['ariadne:svn:revert'];
		$wgWizHeader = $wgWizTitle;
		$wgWizHeaderIcon = $AR->dir->images.'icons/large/svnrevert.png';

		$wgWizStyleSheets = array( $AR->dir->styles."svn.css" );

		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>
