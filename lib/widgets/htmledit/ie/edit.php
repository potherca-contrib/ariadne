<?php
	ldHeader("Content-Type: text/html; charset=UTF-8");

	include($this->store->get_config("code")."nls/ieedit.".$this->reqnls);

?><!--TOOLBAR_START--><!--TOOLBAR_EXEMPT--><!--TOOLBAR_END-->
<!-- Copyright 2000 Microsoft Corporation. All rights reserved. -->
<!-- Author: Steve Isaac, Microsoft Corporation -->
<!-- Changes by Auke van Slooten, Muze V.O.F. to implement source view, add images from Ariadne, stylesheet support, configurable buttons, xhtml. -->
<!-- Changes by Matt Finn, NetDesign Inc which make it work with non-english internet explorers (ParagraphStyles) -->
<html>
<head>
<meta NAME="GENERATOR" CONTENT="Microsoft Visual Studio 6.0">
<META content="text/html; charset=UTF-8" http-equiv=Content-Type>
<title>Edit <?php echo $path.$file; ?></title>

<!-- Styles -->
<link REL="stylesheet" TYPE="text/css" HREF="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/Toolbars/toolbars.css">

<!-- Script Functions and Event Handlers -->
<script LANGUAGE="JavaScript" SRC="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/Inc/dhtmled.js">
</script>

<script LANGUAGE="JavaScript" SRC="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/eolas_patent_workaround.js">
</script>

<script LANGUAGE="JavaScript" SRC="<?php echo $AR->dir->www; ?>widgets/compose/compose.js">
</script>

<script LANGUAGE="JavaScript" SRC="<?php echo $AR->dir->www; ?>widgets/webfx/richedit/getxhtml.js">
</script>

<script LANGUAGE="JavaScript" SRC="<?php echo $AR->dir->www; ?>widgets/webfx/richedit/stringbuilder.js">
</script>

<script ID="clientEventHandlersJS" LANGUAGE="javascript">
<!--
  window.exists=true; // do not reload editing environment if window still exists.
  <?php
    if (!$root) {
      $root=$this->store->get_config("root");
    }
    if (!$path) {
      $path=$this->path;
    }
    if (!$language) {
      $language=$this->nls;
    }
    if (!$value && !$save2form && $this->AR_implements('ppage')) {
      $htmlvalue=$this->GetPage($language, false, true);
    }     
    if (!$name) {
      $name="page";
    }
    if (!$target) {
      global $AR;
      $server_name=ereg_replace('[htpfs]+://','',$AR->host);
      if ($_SERVER["HTTP_HOST"]!=$server_name) {
        // editor called directly from an ariadne hosted site, not via the AR->host
        // so the user credentials cookie is only available there and save must
        // be called on that hostname.
        $target=$this->make_url();
      } else {
        $target=$root.$path;
      }
    }
?>
  var buttons_disabled=new Array();
//  var tbContentEditOptions=new Array();
<?php
	// load editor.ini, in case the editor is started directly, not through the 
	// js.html file
	$options=$this->call("editor.ini");

	function make_ini_options($name, $option) {
		if (is_array($option)) {
			reset($option);
			echo "	$name = new Array();\n";
			while (list($key, $value)=each($option)) {
				make_ini_options($name."[\"$key\"]", $value);
			}
		} else
		if (is_string($option)) {
			echo "	$name = \"".AddCSlashes($option, ARESCAPE)."\";\n";
		} else {
			echo "	$name = ".(int)$option.";\n";
		}
	}

	echo "var ";
	make_ini_options("tbContentEditOptions", $options);

?>
  if (!tbContentEditOptions['editor.ini']) {
    tbContentEditOptions['editor.ini']='<?php echo $this->path; ?>';
  }
  tbContentTarget="<?php echo $target; ?>";
  tbContentRoot="<?php echo $root; ?>";
  tbContentPath="<?php echo $path; ?>";
  tbContentFile="<?php echo $file; ?>";
  tbContentName='<?php echo $name; ?>';  
  tbContentLanguage='<?php echo $language; ?>';
  tbContentType='<?php echo $type; ?>';
  if (!window.opener || !window.opener.wgHTMLEditContent) {
    tbContentValue='<?php echo AddCSlashes($htmlvalue, ARESCAPE); ?>';
  } else {
    tbContentValue='';
  }
  tbContentSave2Form=<?php if ($save2form && $save2form!=='false') echo 'true'; else echo 'false'; ?>;
  tbDetailsSetting=false;
//
// Constants
//
var MENU_SEPARATOR = ""; // Context menu separator

//
// Globals
//

var docComplete = false;
var initialDocComplete = false;

var QueryStatusToolbarButtons = new Array();
var QueryStatusEditMenu = new Array();
var QueryStatusFormatMenu = new Array();
var QueryStatusHTMLMenu = new Array();
var QueryStatusTableMenu = new Array();
var QueryStatusZOrderMenu = new Array();
var ContextMenu = new Array();
var GeneralContextMenu = new Array();
var TableContextMenu = new Array();
var AbsPosContextMenu = new Array();
var blockFormatNames=new ActiveXObject("DEGetBlockFmtNamesParam.DEGetBlockFmtNamesParam");


//
// Utility functions
//

// Constructor for custom object that represents an item on the context menu
function ContextMenuItem(string, cmdId) {
  this.string = string;
  this.cmdId = cmdId;
}

// Constructor for custom object that represents a QueryStatus command and 
// corresponding toolbar element.
function QueryStatusItem(command, element) {
  this.command = command;
  this.element = element;
}


//
// Event handlers
//
function window_onload() {

  // Initialze QueryStatus tables. These tables associate a command id with the
  // corresponding button object. Must be done on window load, 'cause the buttons must exist.
  QueryStatusToolbarButtons[0] = new QueryStatusItem(DECMD_BOLD, document.body.all["DECMD_BOLD"]);
  QueryStatusToolbarButtons[1] = new QueryStatusItem(DECMD_COPY, document.body.all["DECMD_COPY"]);
  QueryStatusToolbarButtons[2] = new QueryStatusItem(DECMD_CUT, document.body.all["DECMD_CUT"]);
  QueryStatusToolbarButtons[3] = new QueryStatusItem(DECMD_HYPERLINK, document.body.all["DECMD_HYPERLINK"]);
  QueryStatusToolbarButtons[4] = new QueryStatusItem(DECMD_INDENT, document.body.all["DECMD_INDENT"]);
  QueryStatusToolbarButtons[5] = new QueryStatusItem(DECMD_ITALIC, document.body.all["DECMD_ITALIC"]);
  QueryStatusToolbarButtons[6] = new QueryStatusItem(DECMD_JUSTIFYLEFT, document.body.all["DECMD_JUSTIFYLEFT"]);
  QueryStatusToolbarButtons[7] = new QueryStatusItem(DECMD_JUSTIFYCENTER, document.body.all["DECMD_JUSTIFYCENTER"]);
  QueryStatusToolbarButtons[8] = new QueryStatusItem(DECMD_JUSTIFYRIGHT, document.body.all["DECMD_JUSTIFYRIGHT"]);
  QueryStatusToolbarButtons[9] = new QueryStatusItem(DECMD_LOCK_ELEMENT, document.body.all["DECMD_LOCK_ELEMENT"]);
  QueryStatusToolbarButtons[10] = new QueryStatusItem(DECMD_MAKE_ABSOLUTE, document.body.all["DECMD_MAKE_ABSOLUTE"]);
  QueryStatusToolbarButtons[11] = new QueryStatusItem(DECMD_ORDERLIST, document.body.all["DECMD_ORDERLIST"]);
  QueryStatusToolbarButtons[12] = new QueryStatusItem(DECMD_OUTDENT, document.body.all["DECMD_OUTDENT"]);
  QueryStatusToolbarButtons[13] = new QueryStatusItem(DECMD_PASTE, document.body.all["DECMD_PASTE"]);
  QueryStatusToolbarButtons[14] = new QueryStatusItem(DECMD_REDO, document.body.all["DECMD_REDO"]);
  QueryStatusToolbarButtons[15] = new QueryStatusItem(DECMD_UNDERLINE, document.body.all["DECMD_UNDERLINE"]);
  QueryStatusToolbarButtons[16] = new QueryStatusItem(DECMD_UNDO, document.body.all["DECMD_UNDO"]);
  QueryStatusToolbarButtons[17] = new QueryStatusItem(DECMD_UNORDERLIST, document.body.all["DECMD_UNORDERLIST"]);
  QueryStatusToolbarButtons[18] = new QueryStatusItem(DECMD_INSERTTABLE, document.body.all["DECMD_INSERTTABLE"]);
  QueryStatusToolbarButtons[19] = new QueryStatusItem(DECMD_INSERTROW, document.body.all["DECMD_INSERTROW"]);
  QueryStatusToolbarButtons[20] = new QueryStatusItem(DECMD_DELETEROWS, document.body.all["DECMD_DELETEROWS"]);
  QueryStatusToolbarButtons[21] = new QueryStatusItem(DECMD_INSERTCOL, document.body.all["DECMD_INSERTCOL"]);
  QueryStatusToolbarButtons[22] = new QueryStatusItem(DECMD_DELETECOLS, document.body.all["DECMD_DELETECOLS"]);
  QueryStatusToolbarButtons[23] = new QueryStatusItem(DECMD_INSERTCELL, document.body.all["DECMD_INSERTCELL"]);
  QueryStatusToolbarButtons[24] = new QueryStatusItem(DECMD_DELETECELLS, document.body.all["DECMD_DELETECELLS"]);
  QueryStatusToolbarButtons[25] = new QueryStatusItem(DECMD_MERGECELLS, document.body.all["DECMD_MERGECELLS"]);
  QueryStatusToolbarButtons[26] = new QueryStatusItem(DECMD_SPLITCELL, document.body.all["DECMD_SPLITCELL"]);
  QueryStatusToolbarButtons[27] = new QueryStatusItem(DECMD_SETFORECOLOR, document.body.all["DECMD_SETFORECOLOR"]);
  QueryStatusToolbarButtons[28] = new QueryStatusItem(DECMD_SETBACKCOLOR, document.body.all["DECMD_SETBACKCOLOR"]);
  QueryStatusToolbarButtons[29] = new QueryStatusItem(DECMD_IMAGE, document.body.all["DECMD_IMAGE"]);
  QueryStatusEditMenu[0] = new QueryStatusItem(DECMD_UNDO, document.body.all["EDIT_UNDO"]);
  QueryStatusEditMenu[1] = new QueryStatusItem(DECMD_REDO, document.body.all["EDIT_REDO"]);
  QueryStatusEditMenu[2] = new QueryStatusItem(DECMD_CUT, document.body.all["EDIT_CUT"]);
  QueryStatusEditMenu[3] = new QueryStatusItem(DECMD_COPY, document.body.all["EDIT_COPY"]);
  QueryStatusEditMenu[4] = new QueryStatusItem(DECMD_PASTE, document.body.all["EDIT_PASTE"]);
  QueryStatusEditMenu[5] = new QueryStatusItem(DECMD_DELETE, document.body.all["EDIT_DELETE"]);
  QueryStatusHTMLMenu[0] = new QueryStatusItem(DECMD_HYPERLINK, document.body.all["HTML_HYPERLINK"]);
  QueryStatusHTMLMenu[1] = new QueryStatusItem(DECMD_IMAGE, document.body.all["HTML_IMAGE"]);
  QueryStatusFormatMenu[0] = new QueryStatusItem(DECMD_FONT, document.body.all["FORMAT_FONT"]);
  QueryStatusFormatMenu[1] = new QueryStatusItem(DECMD_BOLD, document.body.all["FORMAT_BOLD"]);
  QueryStatusFormatMenu[2] = new QueryStatusItem(DECMD_ITALIC, document.body.all["FORMAT_ITALIC"]);
  QueryStatusFormatMenu[3] = new QueryStatusItem(DECMD_UNDERLINE, document.body.all["FORMAT_UNDERLINE"]);
  QueryStatusFormatMenu[4] = new QueryStatusItem(DECMD_JUSTIFYLEFT, document.body.all["FORMAT_JUSTIFYLEFT"]);
  QueryStatusFormatMenu[5] = new QueryStatusItem(DECMD_JUSTIFYCENTER, document.body.all["FORMAT_JUSTIFYCENTER"]);
  QueryStatusFormatMenu[6] = new QueryStatusItem(DECMD_JUSTIFYRIGHT, document.body.all["FORMAT_JUSTIFYRIGHT"]);
  QueryStatusFormatMenu[7] = new QueryStatusItem(DECMD_SETFORECOLOR, document.body.all["FORMAT_SETFORECOLOR"]);
  QueryStatusFormatMenu[8] = new QueryStatusItem(DECMD_SETBACKCOLOR, document.body.all["FORMAT_SETBACKCOLOR"]);
  QueryStatusTableMenu[0] = new QueryStatusItem(DECMD_INSERTTABLE, document.body.all["TABLE_INSERTTABLE"]);
  QueryStatusTableMenu[1] = new QueryStatusItem(DECMD_INSERTROW, document.body.all["TABLE_INSERTROW"]);
  QueryStatusTableMenu[2] = new QueryStatusItem(DECMD_DELETEROWS, document.body.all["TABLE_DELETEROW"]);
  QueryStatusTableMenu[3] = new QueryStatusItem(DECMD_INSERTCOL, document.body.all["TABLE_INSERTCOL"]);
  QueryStatusTableMenu[4] = new QueryStatusItem(DECMD_DELETECOLS, document.body.all["TABLE_DELETECOL"]);
  QueryStatusTableMenu[5] = new QueryStatusItem(DECMD_INSERTCELL, document.body.all["TABLE_INSERTCELL"]);
  QueryStatusTableMenu[6] = new QueryStatusItem(DECMD_DELETECELLS, document.body.all["TABLE_DELETECELL"]);
  QueryStatusTableMenu[7] = new QueryStatusItem(DECMD_MERGECELLS, document.body.all["TABLE_MERGECELL"]);
  QueryStatusTableMenu[8] = new QueryStatusItem(DECMD_SPLITCELL, document.body.all["TABLE_SPLITCELL"]);
  QueryStatusZOrderMenu[0] = new QueryStatusItem(DECMD_SEND_TO_BACK, document.body.all["ZORDER_SENDBACK"]);
  QueryStatusZOrderMenu[1] = new QueryStatusItem(DECMD_BRING_TO_FRONT, document.body.all["ZORDER_BRINGFRONT"]);
  QueryStatusZOrderMenu[2] = new QueryStatusItem(DECMD_SEND_BACKWARD, document.body.all["ZORDER_SENDBACKWARD"]);
  QueryStatusZOrderMenu[3] = new QueryStatusItem(DECMD_BRING_FORWARD, document.body.all["ZORDER_BRINGFORWARD"]);
  QueryStatusZOrderMenu[4] = new QueryStatusItem(DECMD_SEND_BELOW_TEXT, document.body.all["ZORDER_BELOWTEXT"]);
  QueryStatusZOrderMenu[5] = new QueryStatusItem(DECMD_BRING_ABOVE_TEXT, document.body.all["ZORDER_ABOVETEXT"]);
  
  // Initialize the context menu arrays.
  GeneralContextMenu[0] = new ContextMenuItem("<?php echo $ARnls["e_cut"]; ?>", DECMD_CUT);
  GeneralContextMenu[1] = new ContextMenuItem("<?php echo $ARnls["e_copy"]; ?>", DECMD_COPY);
  GeneralContextMenu[2] = new ContextMenuItem("<?php echo $ARnls["e_paste"]; ?>", DECMD_PASTE);
  TableContextMenu[0] = new ContextMenuItem(MENU_SEPARATOR, 0);
  TableContextMenu[1] = new ContextMenuItem("<?php echo $ARnls["e_insertrow"]; ?>", DECMD_INSERTROW);
  TableContextMenu[2] = new ContextMenuItem("<?php echo $ARnls["e_deleterows"]; ?>", DECMD_DELETEROWS);
  TableContextMenu[3] = new ContextMenuItem(MENU_SEPARATOR, 0);
  TableContextMenu[4] = new ContextMenuItem("<?php echo $ARnls["e_insertcol"]; ?>", DECMD_INSERTCOL);
  TableContextMenu[5] = new ContextMenuItem("<?php echo $ARnls["e_deletecols"]; ?>", DECMD_DELETECOLS);
  TableContextMenu[6] = new ContextMenuItem(MENU_SEPARATOR, 0);
  TableContextMenu[7] = new ContextMenuItem("<?php echo $ARnls["e_insertcell"]; ?>", DECMD_INSERTCELL);
  TableContextMenu[8] = new ContextMenuItem("<?php echo $ARnls["e_deletecells"]; ?>", DECMD_DELETECELLS);
  TableContextMenu[9] = new ContextMenuItem("<?php echo $ARnls["e_mergecells"]; ?>", DECMD_MERGECELLS);
  TableContextMenu[10] = new ContextMenuItem("<?php echo $ARnls["e_splitcell"]; ?>", DECMD_SPLITCELL);
  AbsPosContextMenu[0] = new ContextMenuItem(MENU_SEPARATOR, 0);
  AbsPosContextMenu[1] = new ContextMenuItem("<?php echo $ARnls["e_send_to_back"]; ?>", DECMD_SEND_TO_BACK);
  AbsPosContextMenu[2] = new ContextMenuItem("<?php echo $ARnls["e_bring_to_front"]; ?>", DECMD_BRING_TO_FRONT);
  AbsPosContextMenu[3] = new ContextMenuItem(MENU_SEPARATOR, 0);
  AbsPosContextMenu[4] = new ContextMenuItem("<?php echo $ARnls["e_send_backward"]; ?>", DECMD_SEND_BACKWARD);
  AbsPosContextMenu[5] = new ContextMenuItem("<?php echo $ARnls["e_bring_forward"]; ?>", DECMD_BRING_FORWARD);
  AbsPosContextMenu[6] = new ContextMenuItem(MENU_SEPARATOR, 0);
  AbsPosContextMenu[7] = new ContextMenuItem("<?php echo $ARnls["e_send_below_text"]; ?>", DECMD_SEND_BELOW_TEXT);
  AbsPosContextMenu[8] = new ContextMenuItem("<?php echo $ARnls["e_bring_above_text"]; ?>", DECMD_BRING_ABOVE_TEXT);
  docComplete = false;

  // in some cases we need to give IE time to load the dhtml component
  // checking only on the readyState and the Busy flag doesn't seem to
  // be enough
  setTimeout("loadBlockFormat();", 1000);
}

function loadBlockFormat() {
  // object.readyState is a number, not a string, unlike all other readyState properties, doh
  // 4 = complete
  if (tbContentElement.readyState!=4 || tbContentElement.Busy) {
    setTimeout("loadBlockFormat();",500);
  } else {
    tbContentElement.ExecCommand(DECMD_GETBLOCKFMTNAMES,OLECMDEXECOPT_DODEFAULT,blockFormatNames);

    vbarr = new VBArray(blockFormatNames.Names);
    arr = vbarr.toArray();

    // clear styles
    while (ParagraphStyle.length>0) {
      ParagraphStyle.options[ParagraphStyle.length-1]=null;
    }
    // set new ones
    for (var i=0;i<arr.length;i++) {
      ParagraphStyle.options[ParagraphStyle.options.length]=new Option(arr[i], arr[i]);
    }
    tbContentElement.supportsXHTML = tbContentElement.DOM.documentElement && tbContentElement.DOM.childNodes != null;
    tbContentElement.getXHTML = function () {
      if (!tbContentElement.supportsXHTML) {
        alert("Document root node cannot be accessed in IE4.x");
        return;
      }
      else if (typeof window.StringBuilder != "function") {
        alert("StringBuilder is not defined. Make sure to include stringbuilder.js");
        return;
      }

      var sb = new StringBuilder;
      // IE5 and IE55 has trouble with the document node
      var cs = tbContentElement.DOM.childNodes;
      var l = cs.length;
      for (var i = 0; i < l; i++) {
        _appendNodeXHTML(cs[i], sb);
      } 
      return sb.toString();
    }
    loadpage(tbContentRoot, tbContentPath, tbContentFile, tbContentName, tbContentLanguage, tbContentType, tbContentValue, tbContentSave2Form, tbContentTarget, false);
  }
}

function loadpage(root, path, file, name, language, type, value, save2form, target, editoptions) {
  // FIXME check isDirty and ask for save first.
  // window.document.title='Edit '+path+file+' ( '+name+': '+language+')';
  if (target) {
    tbContentTarget=target;
  } else {
    tbContentTarget=root+path;
  }
  tbContentRoot=root;
  tbContentPath=path;
  tbContentFile=file;
  tbContentName=name;
  tbContentLanguage=language;
  tbContentType=type;
  if (!editoptions) {
    if (window.opener && window.opener.wgHTMLEditOptions) {
      tbContentEditOptions=window.opener.wgHTMLEditOptions;
    }
  } else {
    tbContentEditOptions=editoptions;
  }	
  if (tbContentEditOptions["disabled"]) {
    var temp=tbContentEditOptions["disabled"].split(":");
    for (i=0; i<temp.length; i++) {
      if (temp[i]) {
        buttons_disabled[temp[i]]=1;
      }
    }
  }
  if (window.opener && window.opener.wgHTMLEditContent) {
    tbContentValue=new String(window.opener.wgHTMLEditContent.value);
  } else if (value) {
    tbContentValue=value;
  }
  if (tbContentValue=='') {
    tbContentValue=tbContentEditOptions["emptydoc"];
  }
  tbContentSave2Form=save2form;
  if (tbContentValue.match(/<FRAME/i) && (ViewHTML.TBSTATE=="checked")) {
    VIEW_HTML_onclick();
  }
  if (ViewHTML.TBSTATE!="checked") {
    tbContentElement.DocumentHTML=AR_FORMAT_HTML(tbContentValue);
  } else {
    tbContentElement.DocumentHTML=tbContentValue;
    ToolbarFormatState = tbContentEditOptions["toolbars"]["FormatToolbar"] == 2 ? "dockedtop" : "hidden";
    ToolbarFontFormatState = tbContentEditOptions["toolbars"]["FontFormatToolbar"] == 2 ? "dockedtop" : "hidden";
    ToolbarStandardState = tbContentEditOptions["toolbars"]["StandardToolbar"] == 2 ? "dockedtop" : "hidden";
    ToolbarAbsState = tbContentEditOptions["toolbars"]["AbsolutePositioningToolbar"] == 2 ? "dockedtop" : "hidden";
    ToolbarTableState = tbContentEditOptions["toolbars"]["TableToolbar"] == 2 ? "dockedtop" : "hidden";
    toolbars_init();
    TBRebuildMenu(VIEW, true);
  }
  tbContentElement.BaseURL=root+path;
  tbContentElement.onkeypress=wgCompose_keypress;
  tbContentElement.onkeydown=wgCompose_check;
  tbContentElement.focus();
}

function InsertTable() {
  var pVar = ObjTableInfo;
  var args = new Array();
  var arr = null;
   
  // Display table information dialog
  args["NumRows"] = ObjTableInfo.NumRows;
  args["NumCols"] = ObjTableInfo.NumCols;
  args["TableAttrs"] = ObjTableInfo.TableAttrs;
  args["CellAttrs"] = ObjTableInfo.CellAttrs;
  args["Caption"] = ObjTableInfo.Caption;
  
  arr = null;
  
  arr = showModalDialog( "<?php echo $AR->dir->www; ?>widgets/htmledit/ie/Inc/instable.htm",
                             args,
                             "font-family:Verdana; font-size:12; dialogWidth:36em; dialogHeight:25em; status: no; resizable: yes;");
  if (arr != null) {
  
    // Initialize table object
    for ( elem in arr ) {
      if ("NumRows" == elem && arr["NumRows"] != null) {
        ObjTableInfo.NumRows = arr["NumRows"];
      } else if ("NumCols" == elem && arr["NumCols"] != null) {
        ObjTableInfo.NumCols = arr["NumCols"];
      } else if ("TableAttrs" == elem) {
        ObjTableInfo.TableAttrs = arr["TableAttrs"];
      } else if ("CellAttrs" == elem) {
        ObjTableInfo.CellAttrs = arr["CellAttrs"];
      } else if ("Caption" == elem) {
        ObjTableInfo.Caption = arr["Caption"];
      }
    }
    tbContentElement.ExecCommand(DECMD_INSERTTABLE,OLECMDEXECOPT_DODEFAULT, pVar);  
  }
}

function tbContentElement_ShowContextMenu() {
  var menuStrings = new Array();
  var menuStates = new Array();
  var state;
  var i
  var idx = 0;

  // Rebuild the context menu. 
  ContextMenu.length = 0;

  // Always show general menu
  for (i=0; i<GeneralContextMenu.length; i++) {
    ContextMenu[idx++] = GeneralContextMenu[i];
  }

  // Is the selection inside a table? Add table menu if so
  if (tbContentElement.QueryStatus(DECMD_INSERTROW) != DECMDF_DISABLED) {
    for (i=0; i<TableContextMenu.length; i++) {
      ContextMenu[idx++] = TableContextMenu[i];
    }
  }

  // Is the selection on an absolutely positioned element? Add z-index commands if so
  if (tbContentElement.QueryStatus(DECMD_LOCK_ELEMENT) != DECMDF_DISABLED) {
    for (i=0; i<AbsPosContextMenu.length; i++) {
      ContextMenu[idx++] = AbsPosContextMenu[i];
    }
  }

  // Set up the actual arrays that get passed to SetContextMenu
  for (i=0; i<ContextMenu.length; i++) {
    menuStrings[i] = ContextMenu[i].string;
    if (menuStrings[i] != MENU_SEPARATOR) {
      if (buttons_disabled[ContextMenu[i].cmdId]) {
        state = DECMDF_DISABLED;
      } else {
        state = tbContentElement.QueryStatus(ContextMenu[i].cmdId);
      }
    } else {
      state = DECMDF_ENABLED;
    }
    if (state == DECMDF_DISABLED || state == DECMDF_NOTSUPPORTED) {
      menuStates[i] = OLE_TRISTATE_GRAY;
    } else if (state == DECMDF_ENABLED || state == DECMDF_NINCHED) {
      menuStates[i] = OLE_TRISTATE_UNCHECKED;
    } else { // DECMDF_LATCHED
      menuStates[i] = OLE_TRISTATE_CHECKED;
    }
  }
  
  // Set the context menu
  tbContentElement.SetContextMenu(menuStrings, menuStates);
}

function tbContentElement_ContextMenuAction(itemIndex) {
  
  if (ContextMenu[itemIndex].cmdId == DECMD_INSERTTABLE) {
    InsertTable();
  } else {
    tbContentElement.ExecCommand(ContextMenu[itemIndex].cmdId, OLECMDEXECOPT_DODEFAULT);
  }
}

// DisplayChanged handler. Very time-critical routine; this is called
// every time a character is typed. QueryStatus those toolbar buttons that need
// to be in synch with the current state of the document and update. 
function tbContentElement_DisplayChanged() {
  var i, s;

  for (i=0; i<QueryStatusToolbarButtons.length; i++) {
    if (buttons_disabled[QueryStatusToolbarButtons[i].command]) {
      s = DECMDF_DISABLED;
    } else {
      s = tbContentElement.QueryStatus(QueryStatusToolbarButtons[i].command);
    }
    if (s == DECMDF_DISABLED || s == DECMDF_NOTSUPPORTED) {
      TBSetState(QueryStatusToolbarButtons[i].element, "gray"); 
    } else if (s == DECMDF_ENABLED  || s == DECMDF_NINCHED) {
      TBSetState(QueryStatusToolbarButtons[i].element, "unchecked"); 
    } else { // DECMDF_LATCHED
      TBSetState(QueryStatusToolbarButtons[i].element, "checked");
    }
  }

  if (buttons_disabled[DECMD_SETBLOCKFMT]) {
    s = DECMDF_DISABLED;
  } else {
    s = tbContentElement.QueryStatus(DECMD_GETBLOCKFMT);
  }
  if (s == DECMDF_DISABLED || s == DECMDF_NOTSUPPORTED) {
    ParagraphStyle.disabled = true;
  } else {
    ParagraphStyle.disabled = false;
    ParagraphStyle.value = tbContentElement.ExecCommand(DECMD_GETBLOCKFMT, OLECMDEXECOPT_DODEFAULT);
  }
  if (buttons_disabled[DECMD_SETFONTNAME]) {
    s = DECMDF_DISABLED;
  } else {
    s = tbContentElement.QueryStatus(DECMD_GETFONTNAME);
  }
  if (s == DECMDF_DISABLED || s == DECMDF_NOTSUPPORTED) {
    FontName.disabled = true;
  } else {
    FontName.disabled = false;
    FontName.value = tbContentElement.ExecCommand(DECMD_GETFONTNAME, OLECMDEXECOPT_DODEFAULT);
  }
  
  if (s == DECMDF_DISABLED || s == DECMDF_NOTSUPPORTED) {
    FontSize.disabled = true;
  } else {
    FontSize.disabled = false;
    FontSize.value = tbContentElement.ExecCommand(DECMD_GETFONTSIZE, OLECMDEXECOPT_DODEFAULT);
  }

}

function MENU_FILE_SAVE_onclick() {
  if (ViewHTML.TBSTATE=="checked") {
    if (tbContentEditOptions["xhtml"]) {
	  var sContents=tbContentElement.getXHTML();
    } else {
      var sContents=tbContentElement.DocumentHTML;
    }
  } else {
    var sContents=tbContentElement.DOM.body.innerText;
  }
  if (tbContentFile) {
    file=tbContentFile+'/';
  } else {
    file='';
  }
  if (window.opener && !window.opener.closed && window.opener.wgHTMLEditContent) {
    // always update the form if it is available.
    window.opener.wgHTMLEditContent.value=sContents;
  }
  if (tbContentSave2Form) {
    // just show some visual confirmation of saving.
    savewindow=window.open('','savewindow','directories=no,height=100,width=300,location=no,status=no,toolbar=no,resizable=no');
    savewindow.document.open();
    savewindow.document.write("<html><body bgcolor=#CCCCCC><font face='Arial,helvetica,sans-serif'>");
    savewindow.document.write("<br>Saving "+tbContentName+"</font></body></html>");
    savewindow.document.close();
    savewindow.close();
  } else {
    savewindow=window.open('','savewindow','directories=no,height=100,width=300,location=no,status=no,toolbar=no,resizable=no');
    savewindow.document.open();
    savewindow.document.write("<html><body bgcolor=#CCCCCC><font face='Arial,helvetica,sans-serif'>");
    savewindow.document.write("<form method='POST' action='"+tbContentTarget+file+"edit."+tbContentName+".save.phtml'>");
    savewindow.document.write("<input type='hidden' name='"+tbContentName+"'>");
    savewindow.document.write("<input type='hidden' name='ContentEditOptionsPath' value='"+tbContentEditOptions["editor.ini"]+"'>");
    savewindow.document.write("<input type='hidden' name='ContentLanguage'>");
    savewindow.document.write("</form><br>Saving "+tbContentName+"</font></body></html>");
    savewindow.document.close();
    savewindow.document.forms[0][tbContentName].value=sContents;
    savewindow.document.forms[0].ContentLanguage.value=tbContentLanguage;
    savewindow.document.forms[0].submit();
  }
}

function AR_FORMAT_HTML(code) {
  var sContents=new String(code);
  // don't even think about changing the next few lines... 
  // the htmlediting component is extremely picky
  sContents=sContents.replace(/&/g,"&amp;");
  sContents=sContents.replace(/</g,"&lt;");
  sContents=sContents.replace(/>/g,"&gt;");  

  while (sContents.match(/[\n](&nbsp;)* /)) {
    sContents=sContents.replace(/([\n](&nbsp;)*) /, "$1&nbsp;");
  }
  sContents=new String("<HTML><HEAD><META content=\"text/html; charset=UTF-8\" http-equiv=Content-Type><STYLE> P { margin: 0px;} </STYLE></HEAD><BODY STYLE=\"font:10pt courier new, monospace\">"+sContents+"</BODY></HTML>");
  var linebreak=sContents.lastIndexOf('\n');
  while (linebreak!=-1) {
    sContents=sContents.substr(0, linebreak-1)+'<P>'+sContents.substr(linebreak+1); 
    linebreak=sContents.lastIndexOf('\n');
  }
  // now you can edit anything you want...
  return sContents;
}

function toolbars_init() {

  if (tbContentEditOptions["toolbars"]["FormatToolbar"]==0) {
    TBSetState(ToolbarMenuFmt, "gray");
    ToolbarFormatState='hidden';
  } else if (ToolbarFormatState=="hidden") {
    TBSetState(ToolbarMenuFmt, "unchecked");
  } else {
    TBSetState(ToolbarMenuFmt, "checked");
  }
  TBSetState(FormatToolbar, ToolbarFormatState);

  if (tbContentEditOptions["toolbars"]["FontFormatToolbar"]==0) {
    TBSetState(ToolbarMenuFontFmt, "gray");
    ToolbarFontFormatState='hidden';
  } else if (ToolbarFontFormatState=="hidden") {
    TBSetState(ToolbarMenuFontFmt, "unchecked");
  } else {
    TBSetState(ToolbarMenuFontFmt, "checked");
  }
  TBSetState(FontFormatToolbar, ToolbarFontFormatState);

  if (tbContentEditOptions["toolbars"]["AbsolutePositioningToolbar"]==0) {
    TBSetState(ToolbarMenuAbs, "gray");
    ToolbarAbsState='hidden';
  } else if (ToolbarAbsState=="hidden") {
    TBSetState(ToolbarMenuAbs, "unchecked");
  } else {
    TBSetState(ToolbarMenuAbs, "checked");
  }
  TBSetState(AbsolutePositioningToolbar, ToolbarAbsState);

  if (tbContentEditOptions["toolbars"]["TableToolbar"]==0) {
    TBSetState(ToolbarMenuTable, "gray");
    ToolbarTableState='hidden';
  } else if (ToolbarTableState=="hidden") {
    TBSetState(ToolbarMenuTable, "unchecked");
  } else {
    TBSetState(ToolbarMenuTable, "checked");
  }
  TBSetState(TableToolbar, ToolbarTableState);

  if (tbContentEditOptions["toolbars"]["StandardToolbar"]==0) {
    TBSetState(ToolbarMenuStd, "gray");
    ToolbarStandardState='hidden';
  } else if (ToolbarStandardState=="hidden") {
    TBSetState(ToolbarMenuStd, "unchecked");
  } else {
    TBSetState(ToolbarMenuStd, "checked");
  }
  TBSetState(StandardToolbar, ToolbarStandardState);

}

function VIEW_HTML_onclick() {
  if (ViewHTML.TBSTATE=="checked") {

    TBSetState(ViewHTML, "unchecked");
    if (tbContentEditOptions["xhtml"]) {
	  var sContents=tbContentElement.getXHTML();
    } else {
      var sContents=tbContentElement.DocumentHTML;
    }
    sContents=AR_FORMAT_HTML(sContents);
    tbContentElement.DocumentHTML=sContents;

    ToolbarFormatState=FormatToolbar.TBSTATE;
    TBSetState(FormatToolbar, "hidden");
    TBSetState(ToolbarMenuFmt, "gray");    
    ToolbarFontFormatState=FontFormatToolbar.TBSTATE;
    TBSetState(FontFormatToolbar, "hidden");
    TBSetState(ToolbarMenuFontFmt, "gray");    
    ToolbarAbsState=AbsolutePositioningToolbar.TBSTATE;
    TBSetState(AbsolutePositioningToolbar, "hidden");
    TBSetState(ToolbarMenuAbs, "gray");
    ToolbarTableState=TableToolbar.TBSTATE;
    TBSetState(TableToolbar, "hidden");
    TBSetState(ToolbarMenuTable, "gray");
    TBRebuildMenu(ViewHTML.parentElement, true);
    tbDetailsSetting=tbContentElement.ShowDetails;
    tbContentElement.ShowDetails = false;

  } else if (ViewHTML.TBSTATE=="unchecked") {

    var sContents=tbContentElement.DOM.body.innerText;
    if (sContents.match(/<FRAME/i)) {
      alert('HTML contains a frameset\nWYSIWYG view disabled');
    } else {

/*
*/
      toolbars_init();
      tbContentElement.ShowDetails = tbDetailsSetting;
      TBSetState(ViewHTML, "checked");
      TBRebuildMenu(ViewHTML.parentElement, true);
      tbContentElement.DocumentHTML=sContents
      tbContentElement.BaseURL=tbContentRoot+tbContentPath;
    }

  }
  tbContentElement.focus();
}

function DECMD_VISIBLEBORDERS_onclick() {
  tbContentElement.ShowBorders = !tbContentElement.ShowBorders;
  tbContentElement.focus();
}

function DECMD_UNORDERLIST_onclick() {
  tbContentElement.ExecCommand(DECMD_UNORDERLIST,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_UNDO_onclick() {
  tbContentElement.ExecCommand(DECMD_UNDO,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_UNDERLINE_onclick() {
  tbContentElement.ExecCommand(DECMD_UNDERLINE,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_SNAPTOGRID_onclick() {
  tbContentElement.SnapToGrid = !tbContentElement.SnapToGrid;
  tbContentElement.focus();
}

function DECMD_SHOWDETAILS_onclick() {
  tbContentElement.ShowDetails = !tbContentElement.ShowDetails;
  tbContentElement.focus();
}

function DECMD_SETFORECOLOR_onclick() {
  var arr = showModalDialog( "<?php echo $this->store->get_config("root").$this->path;
                ?>edit.object.html.selectcolor.phtml",
                             "",
                             "font-family:Verdana; font-size:12; dialogWidth:30em; dialogHeight:34em; status: no; resizable: yes;" );

  if (arr != null) {
    tbContentElement.ExecCommand(DECMD_SETFORECOLOR,OLECMDEXECOPT_DODEFAULT, arr);
  }
}

function DECMD_SETBACKCOLOR_onclick() {
  var arr = showModalDialog( "<?php echo $this->store->get_config("root").$this->path;
                ?>edit.object.html.selectcolor.phtml",
                             "",
                             "font-family:Verdana; font-size:12; dialogWidth:30em; dialogHeight:34em; status: no; resizable: yes;" );

  if (arr != null) {
    tbContentElement.ExecCommand(DECMD_SETBACKCOLOR,OLECMDEXECOPT_DODEFAULT, arr);
  }
  tbContentElement.focus();
}

function DECMD_SELECTALL_onclick() {
  tbContentElement.ExecCommand(DECMD_SELECTALL,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_REDO_onclick() {
  tbContentElement.ExecCommand(DECMD_REDO,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_PASTE_onclick() {
  tbContentElement.ExecCommand(DECMD_PASTE,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_OUTDENT_onclick() {
  tbContentElement.ExecCommand(DECMD_OUTDENT,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_ORDERLIST_onclick() {
  tbContentElement.ExecCommand(DECMD_ORDERLIST,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_MAKE_ABSOLUTE_onclick() {
  tbContentElement.ExecCommand(DECMD_MAKE_ABSOLUTE,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_LOCK_ELEMENT_onclick() {
  tbContentElement.ExecCommand(DECMD_LOCK_ELEMENT,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_JUSTIFYRIGHT_onclick() {
  tbContentElement.ExecCommand(DECMD_JUSTIFYRIGHT,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_JUSTIFYLEFT_onclick() {
  tbContentElement.ExecCommand(DECMD_JUSTIFYLEFT,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_JUSTIFYCENTER_onclick() {
  tbContentElement.ExecCommand(DECMD_JUSTIFYCENTER,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_ITALIC_onclick() {
  tbContentElement.ExecCommand(DECMD_ITALIC,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_INDENT_onclick() {
  tbContentElement.ExecCommand(DECMD_INDENT,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function getEl(sTag,start) {
  while ((start!=null) && (start.tagName!=sTag))
    start = start.parentElement
  return start
}


function DECMD_IMAGE_onclick() {
  var args = new Array();
  var elIMG = false;
  var el = false;
  var rg = false;

  window.el=false;
  window.elIMG=false;
  window.rg=false;
  el=tbContentElement.DOM.selection;
  window.el=el;
  if (tbContentEditOptions["photobook"] && tbContentEditOptions["photobook"]["location"]) {
    var photobook=tbContentEditOptions["photobook"]["location"];
  } else {
    var photobook='<?php echo $AR->user->path; ?>';
  }
  if (el.type=="Control") {
    elIMG=el.createRange().item(0);
    window.elIMG=elIMG;
    if (elIMG) {
      src=new String(elIMG.src);
      root=new String('<?php echo $this->store->get_config("root"); ?>');
      if (src.substring(0,root.length)==root) {
        src=src.substring(root.length);
      } else { // htmledit component automatically adds http://
        temp=new String('<?php echo $this->store->get_config("root"); ?>');
        if (src.substring(0,temp.length)==temp) {
          src=src.substring(temp.length);
        } else {
          temp=new String('http:///');
          if (src.substring(0,temp.length)==temp) {
            src=src.substring(temp.length-1);
          }
        }
      }
      args['src'] = src;
      args['border'] = elIMG.border;
      args['hspace'] = elIMG.hspace;
      args['vspace'] = elIMG.vspace;
      args['align'] = elIMG.align;
      args['alt'] = elIMG.alt;
      args['ar:type'] = elIMG['ar:type'];
      args['ar:path'] = elIMG['ar:path'];
    }
  } else {
    elIMG=false;
    window.rg=el.createRange();
    args['src'] = "";
    args['ar:path'] = "<?php echo $this->path; ?>";
    args['hspace'] = "";
    args['vspace'] = "";
    args['align'] = ""; 
    args['alt'] = "";
    args['border'] = "";
  }
  args['editOptions'] = tbContentEditOptions;
  args['stylesheet'] = tbContentEditOptions["css"]["stylesheet"];
  arr = showModalDialog( tbContentEditOptions["dialogpath"] + 
	"edit.object.html.image.phtml", args,  "font-family:Verdana; font-size:12; dialogWidth:600px; dialogHeight:400px; status: no; resizable: yes;");
//  window.open(tbContentEditOptions["dialogpath"] + "edit.object.html.image.phtml");
  if (arr != null){
	IMAGE_set(arr);
  }

}

function IMAGE_set(arr) {
  window.setfocusto=false;
  var el=window.el;
  if (arr != null) {
    src=new String(arr['src']);
    temp=new String('http://');
    if (src.substring(0,temp.length)!=temp) {
      <?php
        /* 
          remove the nls part from the store->root var so it won't get
          saved in the image URL
        */
        $root = $this->store->get_config("root");
        $rootoptions = $this->store->get_config("rootoptions");
        if ($ARCurrent->session && $ARCurrent->session->id) {
          ldSetRoot($ARCurrent->session->id);
        } else {
          ldSetRoot();
        }
      ?>
      src='<?php echo $AR->host.$this->store->get_config("root"); ?>'+src;
      <?php
        $this->store->root = $root;
        $this->store->rootoptions = $rootoptions;
      ?>
    }
	temp='<IMG SRC="'+src+'"';
	if (arr['border']!='') {
		temp+=' BORDER="'+arr['border']+'"';
	}
	if (arr['hspace']!='') {
	  temp+=' HSPACE="'+arr['hspace']+'"';
	}
	if (arr['vspace']!='') {
	  temp+=' VSPACE="'+arr['vspace']+'"';
	}
	if (arr['align']!='') {
	  temp+=' ALIGN="'+arr['align']+'"';
	}
	if (arr['alt']!='') {
	  temp+=' ALT="'+arr['alt']+'"';
	}
	if (arr['class']!='') {
		temp+=' CLASS="'+arr['class']+'"';
	}
	if (arr['ar:type']!='') {
		temp+=' ar:type="'+arr['ar:type']+'"';
	}
	if (arr['ar:path']!='') {
		temp+=' ar:path="'+arr['ar:path']+'"';
	}
	temp+='>';
    if (window.elIMG) { // insert a new img
      window.elIMG.outerHTML=temp;
/*
      elIMG=window.elIMG;
      elIMG.src=src;
      elIMG.border=arr['border'];
      elIMG.hspace=arr['hspace'];
      elIMG.vspace=arr['vspace'];
      if (arr['align']=='none') {
        elIMG.align='';
      } else {
        elIMG.align=arr['align'];
      }
      elIMG.alt=arr['alt'];
*/
    } else {
      el=window.el;
      if ((el.type=="None") || (el.type=="Text"))  {
/*
        temp='<IMG SRC="'+src+'"';
        if (arr['border']!='') {
          temp+=' BORDER='+arr['border'];
        }
        if (arr['hspace']!='') {
          temp+=' HSPACE='+arr['hspace'];
        }
        if (arr['vspace']!='') {
          temp+=' VSPACE='+arr['vspace'];
        }
        if (arr['align']!='') {
          temp+=' ALIGN='+arr['align'];
        }
        if (arr['alt']!='') {
          temp+=' ALT="'+arr['alt']+'"';
        }
        temp+='>';
*/
        rg.pasteHTML(temp);
        rg.select();
      }
    }
  }
}  

function wgCompose_show(buffer) {
  el=tbContentElement.DOM.selection;
  // el.clear();
  rg=el.createRange();
  rg.pasteHTML(buffer);
}

function GetElement(oElement,sTag) 
{
  /*Utility function; Goes up the DOM from the element oElement, till
  a parent element with the tag that matches sTag
  is found. Returns that parent element.*/
  while (oElement!=null && oElement.tagName!=sTag){
    oElement = oElement.parentElement;
  }
  return oElement;
}

function DECMD_HYPERLINK_onclick() 
{
	var arr,args,oSel, oParent, sType;
	var oATag=false;

	oSel = tbContentElement.DOM.selection;
	oRange = oSel.createRange();
	sType=oSel.type;
	if (sType=="Control") {
		oElement=oRange.item(0);
	} else {
		// check if the range is partly over an existing tag
		// fail in that case
		if (oRange.htmlText.substr(oRange.htmlText.length-4, 4)=='<BR>') {
			// BR included in selection as last element, remove it, it has
			// dangerous effects on the hyperlink command in IE
			oRange.moveEnd('character',-1);
			oRange.select();
		}
		if (oRange.htmlText.substr(0,4)=='<BR>') {
			// idem when its the first character
			oRange.moveStart('character',1);
			oRange.select();
		}
		oElement=oRange;
	}
	
	oParent=oElement.parentElement();

	// Loop through the parent elements until we get the
	// link tag
	while (oParent.tagName!="A" && oParent.parentElement) {
		oParent=oParent.parentElement;
	}

	arr=null;
	args=new Array();
	//set a default value for your link button
	args["URL"] = "http:/"+"/";
	args["anchors"] = HYPERLINK_getAnchors();
  	if (oParent.tagName=="A") {
		oATag=oParent;
		args["URL"] = oParent.href;
		args['name'] = oParent.name;
		for (var i=0; i<oParent.attributes.length; i++) {
			var oAttr=oParent.attributes.item(i);
			if (oAttr.specified) {
				args[oAttr.nodeName]=oAttr.nodeValue;
			}
		}
	}
	args["tbContentLanguage"]=tbContentLanguage;
	/* 
	here popup your own dialog, pass the arg array to that, get what the user
	entered there and come back here
    tbContentTarget may not yet exist, so call the dialogs on the safe dialogpath
    this path should always exist.
	*/ 
	arr = showModalDialog(tbContentEditOptions["dialogpath"] + 
		"edit.object.html.link.phtml", args,  "font-family:Verdana; font-size:12; dialogWidth:32em; dialogHeight:16em; status: no; resizable: yes;");
	if (arr != null){
		var newLink='<a';
		if (arr['URL']) {
			newLink+=" href=\""+arr['URL']+"\"";
		}
		if (arr['name']) {
			newLink+=' name="'+arr['name']+'"';
		}
		if (arr['attributes']) {
			for (var i in arr['attributes']) {
				var arAttribute=arr['attributes'][i];
				newLink=newLink+" "+arAttribute.name+"=\""+arAttribute.value+"\"";
			}
		}
		newLink=newLink+">";
	    if (!oATag && (arr['URL'] || arr['name'])) {
			if (sType=='Control') {
				oElement.outerHTML=newLink+oElement.outerHTML+"</A>";
			} else {

				// first let the dhtmledit component set the link, since it is better in it.
				// but to find it back, we need a unique identifier
				var linkIdentifier=Math.floor(Math.random()*10000);
				tbContentElement.ExecCommand(DECMD_HYPERLINK, OLECMDEXECOPT_DONTPROMPTUSER, '#'+linkIdentifier);
				// now collapse the range, so even if the range overlaps a link partly, the parent
				// element will become the link. trust me.... 
				oRange.collapse();
				// now set the ATag object, so it can be 'fixed' with the extra attributes later
				oATag=oRange.parentElement();
				if (oATag.tagName!='A' || oATag.href!='#'+linkIdentifier) {
					// ok, the link doesn't line up with the range, apparantly, so try to find the link
					oATag=null;
					var allATags=tbContentElement.DOM.getElementsByTagName('A');
					for (var i=0; i<allATags.length; i++) {
						if (allATags[i].href=='#'+linkIdentifier) {
							oATag=allATags[i];
							break;
						}
					}
				}
			}
		}
		if (oATag && (arr['URL'] || arr['name'])) {
			oATag.outerHTML=newLink+oATag.innerHTML+'</a>';
		}
		if (oATag && !arr['URL'] && !arr['name']) {
			oATag.outerHTML=oATag.innerHTML;
		}
		// this is needed since otherwise if the link contains images and text, the image 'disappears'
		// although still in the HTML...
		// FIXME: only do this when you also keep the selection or at least the cursor position the same...
		// 	 for now just inform users.
		//		tbContentElement.DocumentHTML=tbContentElement.DocumentHTML;
	}
	tbContentElement.focus();
}

function HYPERLINK_getAnchors() {
	var aATags = tbContentElement.DOM.getElementsByTagName('A');
	var result = new Array();
	var i=0;
	var ii=0;
	for (ii=0; ii<aATags.length; ii++) {
		var oATag=aATags[ii];
		if (oATag.name) {
			result[i]='#'+oATag.name;
			i++;
		}
	}	
	return result;
}

function DECMD_FINDTEXT_onclick() {
  tbContentElement.ExecCommand(DECMD_FINDTEXT,OLECMDEXECOPT_PROMPTUSER);
  tbContentElement.focus();
}

function DECMD_DELETE_onclick() {
  tbContentElement.ExecCommand(DECMD_DELETE,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_CUT_onclick() {
  tbContentElement.ExecCommand(DECMD_CUT,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_COPY_onclick() {
  tbContentElement.ExecCommand(DECMD_COPY,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_BOLD_onclick() {
  tbContentElement.ExecCommand(DECMD_BOLD,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function OnMenuShow(QueryStatusArray, menu) {
  var i, s;
 
  for (i=0; i<QueryStatusArray.length; i++) {
    s = tbContentElement.QueryStatus(QueryStatusArray[i].command);
    if (s == DECMDF_DISABLED || s == DECMDF_NOTSUPPORTED) {
      TBSetState(QueryStatusArray[i].element, "gray"); 
    } else if (s == DECMDF_ENABLED  || s == DECMDF_NINCHED) {
       TBSetState(QueryStatusArray[i].element, "unchecked"); 
    } else { // DECMDF_LATCHED
       TBSetState(QueryStatusArray[i].element, "checked");
    }
  }

  // If the menu is the HTML menu, then
  // check if the selection type is "Control", if so,
  // set menu item state of the Intrinsics submenu and rebuild the menu.
  if (QueryStatusArray[0].command == DECMD_HYPERLINK) { 
    for (i=0; i < HTML_INTRINSICS.all.length; i++) {
      if (HTML_INTRINSICS.all[i].className == "tbMenuItem") {    
        if (tbContentElement.DOM.selection.type == "Control") {
            TBSetState(HTML_INTRINSICS.all[i], "gray");  
        } else {
            TBSetState(HTML_INTRINSICS.all[i], "unchecked");  
        }
      }
    }
  }

  // rebuild the menu so that menu item states will be reflected
  TBRebuildMenu(menu, true);
  
  tbContentElement.focus();
}

function INTRINSICS_onclick(html) {
  var selection;
  
  selection = tbContentElement.DOM.selection.createRange();
  selection.pasteHTML(html);
  tbContentElement.focus();
}

function TABLE_DELETECELL_onclick() {
  tbContentElement.ExecCommand(DECMD_DELETECELLS,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function TABLE_DELETECOL_onclick() {
  tbContentElement.ExecCommand(DECMD_DELETECOLS,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function TABLE_DELETEROW_onclick() {
  tbContentElement.ExecCommand(DECMD_DELETEROWS,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function TABLE_INSERTCELL_onclick() {
  tbContentElement.ExecCommand(DECMD_INSERTCELL,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function TABLE_INSERTCOL_onclick() {
  tbContentElement.ExecCommand(DECMD_INSERTCOL,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function TABLE_INSERTROW_onclick() {
  tbContentElement.ExecCommand(DECMD_INSERTROW,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function TABLE_INSERTTABLE_onclick() {
  InsertTable();
  tbContentElement.focus();
}

function TABLE_MERGECELL_onclick() {
  tbContentElement.ExecCommand(DECMD_MERGECELLS,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function TABLE_SPLITCELL_onclick() {
  tbContentElement.ExecCommand(DECMD_SPLITCELL,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function FORMAT_FONT_onclick() {
  tbContentElement.ExecCommand(DECMD_FONT,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function ZORDER_ABOVETEXT_onclick() {
  tbContentElement.ExecCommand(DECMD_BRING_ABOVE_TEXT, OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function ZORDER_BELOWTEXT_onclick() {
  tbContentElement.ExecCommand(DECMD_SEND_BELOW_TEXT, OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function ZORDER_BRINGFORWARD_onclick() {
  tbContentElement.ExecCommand(DECMD_BRING_FORWARD, OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function ZORDER_BRINGFRONT_onclick() {
  tbContentElement.ExecCommand(DECMD_BRING_TO_FRONT, OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function ZORDER_SENDBACK_onclick() {
  tbContentElement.ExecCommand(DECMD_SEND_TO_BACK, OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function ZORDER_SENDBACKWARD_onclick() {
  tbContentElement.ExecCommand(DECMD_SEND_BACKWARD, OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function TOOLBARS_onclick(toolbar, menuItem) {
  if (toolbar.TBSTATE == "hidden") {
    TBSetState(toolbar, "dockedTop");
    TBSetState(menuItem, "checked");
  } else {
    TBSetState(toolbar, "hidden");
    TBSetState(menuItem, "unchecked");
  }
  
  TBRebuildMenu(menuItem.parentElement, false);

  tbContentElement.focus();
}

function ParagraphStyle_onchange() {
  tbContentElement.ExecCommand(DECMD_SETBLOCKFMT, OLECMDEXECOPT_DODEFAULT, ParagraphStyle.value);
  tbContentElement.focus();
}

function FontName_onchange() {
  tbContentElement.ExecCommand(DECMD_SETFONTNAME, OLECMDEXECOPT_DODEFAULT, FontName.value);
  tbContentElement.focus();
}

function FontSize_onchange() {
  tbContentElement.ExecCommand(DECMD_SETFONTSIZE, OLECMDEXECOPT_DODEFAULT, parseInt(FontSize.value));
  tbContentElement.focus();
}

function loadStyleSheet() {
  if (tbContentElement.DOM.styleSheets.length==0) {
    tbContentElement.DOM.createStyleSheet(tbContentEditOptions["css"]["stylesheet"], 0);
  }
}

function init_cssStyle() {
  var inline = tbContentEditOptions['css']['inline'];
  cssStyle.options[0] = new Option('Styles', '');
  cssStyle.options[1] = new Option('Clear', '');
  var i=0;
  for (var istyle in inline) {
    cssStyle.options[i+2] = new Option(inline[istyle], istyle);
    i++;
  }
}

function decCommand(cmdId, cmdExecOpt, url)
{
	var status = tbContentElement.QueryStatus(cmdId) ;
	
	if ( status != DECMDF_DISABLED && status != DECMDF_NOTSUPPORTED )
	{
		if (cmdExecOpt == null) cmdExecOpt = OLECMDEXECOPT_DODEFAULT ;
		tbContentElement.ExecCommand(cmdId, cmdExecOpt, url) ;
	}
	tbContentElement.focus() ;
}

function cssStyle_onChange(command)
{
	/*
		following code is inspired if not copied from the very 
		nicely done FCK editor: http://www.fredck.com/FCKeditor/
	*/	

	var oSelection = tbContentElement.DOM.selection ;
	var oTextRange = oSelection.createRange() ;

	var sTag = new String(command.value);
	var aTagAndClass = sTag.split('.');
	if (aTagAndClass[0]) 
	{
		sTag = aTagAndClass[0];
	} 
	else 
	{
		sTag = "";
	}
	if (aTagAndClass.length==2) 
	{
		var sClass = aTagAndClass[1]; 
	} 
	else 
	{
		var sClass = "";
	}

	if (oSelection.type == "Text")
	{
		var oSpan = document.createElement("SPAN") ;
		oSpan.innerHTML = oTextRange.htmlText ;

		var oParent = oTextRange.parentElement() ;
		var oFirstChild = oSpan.firstChild ;

		if (sTag=='' && oFirstChild.nodeType == 1 && oFirstChild.outerHTML == oSpan.innerHTML && 
				(oFirstChild.tagName == "SPAN"
				|| oFirstChild.tagName == "FONT"
				|| oFirstChild.tagName == "P"
				|| oFirstChild.tagName == "DIV"))
		{
			if (!command.value) 
			{
				if (oFirstChild.tagName=="SPAN") 
				{
					oParent.outerHTML = oParent.innerHTML;
				} else {
					oParent.className = null;
				}
			} else {
				oParent.className = sClass ;
			}
		}
		else
		{
			if (!command.value) 
			{
				var text = oSpan.innerText;
				oTextRange.pasteHTML(text);
			} 
			else
			{ 
				var text = oTextRange.htmlText;
				if (sTag=='') 
				{
					sTag='span';
				}
				if (sClass) 
				{
					var tagStart='<'+sTag+' class="' + sClass + '">';
					var tagEnd='</'+sTag+'>';
				} 
				else 
				{
					var tagStart='<'+sTag+'>';
					var tagEnd='</'+sTag+'>';
				}

				if (oFirstChild && oFirstChild.nodeType==1) {
					// should only be true when the selection
					// contains full or partial block elements
					// FIXME: sorry.. not true. we'll need to do a better job
					// match the parentElement / firstChil against the known block elements?
					// probleem: <p> a <big> b c </big> d </p><p> e f</p>
					// selectie: <p>   <big>   c </big> d </p><p> e  </p>
					// firstChild <p>, but <big> is also split!
					// same probably goes for the end of the string
					// do this recursively? aargh

					oTextStart=oTextRange.duplicate();
					oTextStart.collapse(true);

					var currElement=oTextStart.parentElement();
					var currSelectElement=oFirstChild;
					while (currSelectElement) {
						tempSelectHTML=new String(currSelectElement.innerHTML);
						tempHTML=new String(currElement.innerHTML);
						tempHTML=tempHTML.replace(tempSelectHTML, tagStart+tempSelectHTML+tagEnd);
						currElement.innerHTML=tempHTML;
						currElement=currElement.nextSibling;
						currSelectElement=currSelectElement.nextSibling;
					}
				} else {
					// no (partial) block elements
					// so safely use pasteHTML
					oTextRange.pasteHTML(tagStart+oSpan.innerHTML+tagEnd);	
				}
			}
		}
	}
	else if (oSelection.type == "Control" && oTextRange.length == 1)
	{
		var oControl = oTextRange.item(0) ;
		if (sTag=='' || oControl.tagName==sTag) 
		{
			oControl.className = sClass ;
		}
	}
	command.selectedIndex = 0 ;	
	tbContentElement.focus();
}

function tbContentElement_DocumentComplete() {

    if (initialDocComplete) {
      if (tbContentElement.CurrentDocumentPath == "") {
        URL_VALUE = "http://";
    }
    else {
      URL_VALUE = tbContentElement.CurrentDocumentPath;
    }
  }
  init_cssStyle();
  if (ViewHTML.TBSTATE=="checked") {
    loadStyleSheet();
  }
  initialDocComplete = true;
  docComplete = true;
}

//-->
</script>

</head>
<body LANGUAGE="javascript" onload="return window_onload()" unselectable='on'>

<!-- Toolbars -->
<div unselectable='on' class="tbToolbar" ID="MenuBar">
  <div unselectable='on' class="tbMenu" ID="FILE">
    <?php echo $ARnls["e_file"]; ?>
    <div unselectable='on' class="tbMenuItem" ID="FILE_SAVE" LANGUAGE="javascript" onclick="return MENU_FILE_SAVE_onclick()">
      <?php echo $ARnls["e_save_file"]; ?>
      <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/save.gif" WIDTH="23" HEIGHT="22">
    </div>
  </div> 
  
  <div unselectable='on' class="tbMenu" ID="EDIT" LANGUAGE="javascript" tbOnMenuShow="return OnMenuShow(QueryStatusEditMenu, EDIT)">
    <?php echo $ARnls["e_edit"]; ?>
    <div unselectable='on' class="tbMenuItem" ID="EDIT_UNDO" LANGUAGE="javascript" onclick="return DECMD_UNDO_onclick()">
      <?php echo $ARnls["e_undo"]; ?>
      <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/undo.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div unselectable='on' class="tbMenuItem" ID="EDIT_REDO" LANGUAGE="javascript" onclick="return DECMD_REDO_onclick()">
      <?php echo $ARnls["e_redo"]; ?>
      <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/redo.gif" WIDTH="23" HEIGHT="22">
    </div>

    <div unselectable='on' class="tbSeparator"></div>

    <div unselectable='on' class="tbMenuItem" ID="EDIT_CUT" LANGUAGE="javascript" onclick="return DECMD_CUT_onclick()">
      <?php echo $ARnls["e_cut"]; ?>
      <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/cut.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div unselectable='on' class="tbMenuItem" ID="EDIT_COPY" LANGUAGE="javascript" onclick="return DECMD_COPY_onclick()">
      <?php echo $ARnls["e_copy"]; ?>
      <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/copy.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div unselectable='on' class="tbMenuItem" ID="EDIT_PASTE" LANGUAGE="javascript" onclick="return DECMD_PASTE_onclick()">
      <?php echo $ARnls["e_paste"]; ?>
      <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/paste.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div unselectable='on' class="tbMenuItem" ID="EDIT_DELETE" LANGUAGE="javascript" onclick="return DECMD_DELETE_onclick()">
      <?php echo $ARnls["e_delete"]; ?>
      <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/delete.gif" WIDTH="23" HEIGHT="22">
    </div>

    <div unselectable='on' class="tbSeparator"></div>
    
    <div unselectable='on' class="tbMenuItem" ID="EDIT_SELECTALL" LANGUAGE="javascript" onclick="return DECMD_SELECTALL_onclick()">
      <?php echo $ARnls["e_select_all"]; ?>
    </div>

    <div unselectable='on' class="tbSeparator"></div>

    <div unselectable='on' class="tbMenuItem" ID="EDIT_FINDTEXT" TITLE="Find" LANGUAGE="javascript" onclick="return DECMD_FINDTEXT_onclick()">
      <?php echo $ARnls["e_find"]; ?>
      <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/find.gif" WIDTH="23" HEIGHT="22">
    </div>
  </div>
  
  <div unselectable='on' class="tbMenu" ID="VIEW">
    <?php echo $ARnls["e_view"]; ?>
    <div unselectable='on' class="tbSubmenu" TBTYPE="toggle" ID="VIEW_TOOLBARS">
      <?php echo $ARnls["e_toolbars"]; ?>
      <div unselectable='on' class="tbMenuItem" id="ToolbarMenuStd" TBTYPE="toggle" TBSTATE="checked" ID="TOOLBARS_STANDARD" TBTYPE="toggle" LANGUAGE="javascript" onclick="return TOOLBARS_onclick(StandardToolbar, ToolbarMenuStd)">
        <?php echo $ARnls["e_standard"]; ?>
      </div>
      <div unselectable='on' class="tbMenuItem" id="ToolbarMenuFmt" TBTYPE="toggle" TBSTATE="checked" ID="TOOLBARS_FORMAT" TBTYPE="toggle" LANGUAGE="javascript" onclick="return TOOLBARS_onclick(FormatToolbar, ToolbarMenuFmt)">
        <?php echo $ARnls["e_formatting"]; ?>
      </div>
      <div unselectable='on' class="tbMenuItem" id="ToolbarMenuFontFmt" TBTYPE="toggle" TBSTATE="unchecked" ID="TOOLBARS_FORMAT" TBTYPE="toggle" LANGUAGE="javascript" onclick="return TOOLBARS_onclick(FontFormatToolbar, ToolbarMenuFontFmt)">
        <?php echo $ARnls["e_fontformat"]; ?>
      </div>
      <div unselectable='on' class="tbMenuItem" id="ToolbarMenuAbs" TBTYPE="toggle" TBSTATE="unchecked" ID="TOOLBARS_ZORDER" TBTYPE="toggle" LANGUAGE="javascript" onclick="return TOOLBARS_onclick(AbsolutePositioningToolbar, ToolbarMenuAbs)">
        <?php echo $ARnls["e_absolute_positioning"]; ?>
      </div>
      <div unselectable='on' class="tbMenuItem" id="ToolbarMenuTable" TBTYPE="toggle" TBSTATE="unchecked" ID="TOOLBARS_TABLE" TBTYPE="toggle" LANGUAGE="javascript" onclick="return TOOLBARS_onclick(TableToolbar, ToolbarMenuTable)">
        <?php echo $ARnls["e_table"]; ?>
      </div>
    </div>

     <div unselectable='on' class="tbSeparator"></div>

    <div unselectable='on' class="tbMenuItem" id="ViewHTML" TBTYPE="toggle" TBSTATE="checked" ID="VIEW_HTML" TBTYPE="toggle" LANGUAGE="javascript" onclick="return VIEW_HTML_onclick()">
      WYSIWYG
    </div>
  </div> 
  
<!--
  <div unselectable='on' class="tbMenu" ID="FORMAT" LANGUAGE="javascript" tbOnMenuShow="return OnMenuShow(QueryStatusFormatMenu, FORMAT)">
    Format
    <div unselectable='on' class="tbMenuItem" ID="FORMAT_FONT" LANGUAGE="javascript" onclick="return FORMAT_FONT_onclick()">
      Font...
    </div>
  
    <div unselectable='on' class="tbSeparator"></div>

    <div unselectable='on' class="tbMenuItem" ID="FORMAT_BOLD" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_BOLD_onclick()">
      Bold 
      <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/bold.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div unselectable='on' class="tbMenuItem" ID="FORMAT_ITALIC" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_ITALIC_onclick()">
      Italic
      <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/italic.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div unselectable='on' class="tbMenuItem" ID="FORMAT_UNDERLINE" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_UNDERLINE_onclick()">
      Underline
      <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/under.gif" WIDTH="23" HEIGHT="22">
    </div>
  
    <div unselectable='on' class="tbSeparator"></div>

    <div unselectable='on' class="tbMenuItem" ID="FORMAT_SETFORECOLOR" LANGUAGE="javascript" onclick="return DECMD_SETFORECOLOR_onclick()">
      Set Foreground Color...
      <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/fgcolor.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div unselectable='on' class="tbMenuItem" ID="FORMAT_SETBACKCOLOR" LANGUAGE="javascript" onclick="return DECMD_SETBACKCOLOR_onclick()">
      Set Background Color...
      <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/bgcolor.gif" WIDTH="23" HEIGHT="22">
    </div>
  
    <div unselectable='on' class="tbSeparator"></div>

    <div unselectable='on' class="tbMenuItem" ID="FORMAT_JUSTIFYLEFT" TBTYPE="radio" NAME="Justify" LANGUAGE="javascript" onclick="return DECMD_JUSTIFYLEFT_onclick()">
      Align Left
      <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/left.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div unselectable='on' class="tbMenuItem" ID="FORMAT_JUSTIFYCENTER" TBTYPE="radio" NAME="Justify" LANGUAGE="javascript" onclick="return DECMD_JUSTIFYCENTER_onclick()">
      Center
      <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/center.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div unselectable='on' class="tbMenuItem" ID="FORMAT_JUSTIFYRIGHT" TBTYPE="radio" NAME="Justify" LANGUAGE="javascript" onclick="return DECMD_JUSTIFYRIGHT_onclick()">
      Align Right
      <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/right.gif" WIDTH="23" HEIGHT="22">
    </div> 
  </div>   
  
  <div unselectable='on' class="tbMenu" ID="HTML" LANGUAGE="javascript" tbOnMenuShow="return OnMenuShow(QueryStatusHTMLMenu, HTML)">
    HTML
    <div unselectable='on' class="tbMenuItem" ID="HTML_HYPERLINK" LANGUAGE="javascript" onclick="return DECMD_HYPERLINK_onclick()">
      Link...
      <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/link.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div unselectable='on' class="tbMenuItem" ID="HTML_IMAGE" LANGUAGE="javascript" onclick="return DECMD_IMAGE_onclick()">
      Image...
      <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/image.gif" WIDTH="23" HEIGHT="22">
    </div>

    <div unselectable='on' class="tbSeparator"></div>

    <div unselectable='on' class="tbSubmenu" ID="HTML_INTRINSICS">
      Intrinsics
      <div unselectable='on' class="tbMenuItem" ID="INTRINSICS_TEXTBOX" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;INPUT type=text&gt;')">
        Textbox
      </div>
      <div unselectable='on' class="tbMenuItem" ID="INTRINSICS_PASSWRD" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;INPUT type=password&gt;')">
        Password
      </div>
      <div unselectable='on' class="tbMenuItem" ID="INTRINSICS_FILE" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;INPUT type=file&gt;')">
        File Field
      </div>
      <div unselectable='on' class="tbMenuItem" ID="INTRINSICS_TEXTAREA" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;TEXTAREA rows=2 cols=20&gt;&lt;/TEXTAREA&gt;')">
        Text Area
      </div>

      <div unselectable='on' class="tbSeparator"></div>

      <div unselectable='on' class="tbMenuItem" ID="INTRINSICS_CHECKBOX" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;INPUT type=checkbox&gt;')">
        Checkbox
      </div>
      <div unselectable='on' class="tbMenuItem" ID="INTRINSICS_RADIO" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;INPUT type=radio&gt;')">
        Radio Button
      </div>
 
      <div unselectable='on' class="tbSeparator"></div>

      <div unselectable='on' class="tbMenuItem" ID="INTRINSICS_DROPDOWN" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;SELECT&gt;&lt;/SELECT&gt;')">
        Dropdown
      </div>
      <div unselectable='on' class="tbMenuItem" ID="INTRINSICS_LISTBOX" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;SELECT size=2&gt;&lt;/SELECT&gt;')">
        Listbox
      </div>
 
      <div unselectable='on' class="tbSeparator"></div>

      <div unselectable='on' class="tbMenuItem" ID="INTRINSICS_BUTTON" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;INPUT type=button value=Button&gt;')">
        Button
      </div>
      <div unselectable='on' class="tbMenuItem" ID="INTRINSICS_SUBMIT" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;INPUT type=submit value=Submit&gt;')">
        Submit Button
      </div>
      <div unselectable='on' class="tbMenuItem" ID="INTRINSICS_RESET" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;INPUT type=reset value=Reset&gt;')">
        Reset Button
      </div>
    </div>
  </div>
-->
</div>

<div unselectable='on' class="tbToolbar" ID="StandardToolbar">
  <div unselectable='on' class="tbButton" ID="MENU_FILE_SAVE" TITLE="<?php echo $ARnls["e_save_file"]; ?>" LANGUAGE="javascript" onclick="return MENU_FILE_SAVE_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/save.gif" WIDTH="23" HEIGHT="22">
  </div>
  
  <div unselectable='on' class="tbSeparator"></div>

  <div unselectable='on' class="tbButton" ID="DECMD_CUT" TITLE="<?php echo $ARnls["e_cut"]; ?>" LANGUAGE="javascript" onclick="return DECMD_CUT_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/cut.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div unselectable='on' class="tbButton" ID="DECMD_COPY" TITLE="<?php echo $ARnls["e_copy"]; ?>" LANGUAGE="javascript" onclick="return DECMD_COPY_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/copy.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div unselectable='on' class="tbButton" ID="DECMD_PASTE" TITLE="<?php echo $ARnls["e_paste"]; ?>" LANGUAGE="javascript" onclick="return DECMD_PASTE_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/paste.gif" WIDTH="23" HEIGHT="22">
  </div>

  <div unselectable='on' class="tbSeparator"></div>

  <div unselectable='on' class="tbButton" ID="DECMD_UNDO" TITLE="<?php echo $ARnls["e_undo"]; ?>" LANGUAGE="javascript" onclick="return DECMD_UNDO_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/undo.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div unselectable='on' class="tbButton" ID="DECMD_REDO" TITLE="<?php echo $ARnls["e_redo"]; ?>" LANGUAGE="javascript" onclick="return DECMD_REDO_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/redo.gif" WIDTH="23" HEIGHT="22">
  </div>

  <div unselectable='on' class="tbSeparator"></div>

  <div unselectable='on' class="tbButton" ID="DECMD_FINDTEXT" TITLE="<?php echo $ARnls["e_find"]; ?>" LANGUAGE="javascript" onclick="return DECMD_FINDTEXT_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/find.gif" WIDTH="23" HEIGHT="22">
  </div>
</div>



<div unselectable='on' class="tbToolbar" ID="FontFormatToolbar" TBSTATE='hidden'>
  <select ID="FontName" class="tbGeneral" style="width:140" TITLE="Font Name" LANGUAGE="javascript" onchange="return FontName_onchange()">
    <option value="Arial">Arial
    <option value="Tahoma">Tahoma
    <option value="Courier New">Courier New
    <option value="Times New Roman">Times New Roman
    <option value="Wingdings">Wingdings
  </select>
  <select ID="FontSize" class="tbGeneral" style="width:40" TITLE="Font Size" LANGUAGE="javascript" onchange="return FontSize_onchange()">
    <option value="1">1
    <option value="2">2
    <option value="3">3
    <option value="4">4
    <option value="5">5
    <option value="6">6
    <option value="7">7
  </select>

  <div unselectable='on' class="tbSeparator"></div>

  <div unselectable='on' class="tbButton" ID="DECMD_SETFORECOLOR" TITLE="<?php echo $ARnls["e_foreground_color"]; ?>" LANGUAGE="javascript" onclick="return DECMD_SETFORECOLOR_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/fgcolor.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div unselectable='on' class="tbButton" ID="DECMD_SETBACKCOLOR" TITLE="<?php echo $ARnls["e_background_color"]; ?>" LANGUAGE="javascript" onclick="return DECMD_SETBACKCOLOR_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/bgcolor.gif" WIDTH="23" HEIGHT="22">
  </div>
</div>  

<div unselectable='on' class="tbToolbar" ID="FormatToolbar">
  <select ID="ParagraphStyle" class="tbGeneral" style="width:90" TITLE="Paragraph Format" LANGUAGE="javascript" onchange="return ParagraphStyle_onchange()">
  </select>

  <select ID="cssStyle" class="tbGeneral" style="width:90" TITLE="Style" LANGUAGE="javascript" onchange="return cssStyle_onChange(this)">  
  </select>

  <div unselectable='on' class="tbSeparator"></div>

  <div unselectable='on' class="tbButton" ID="DECMD_BOLD" TITLE="<?php echo $ARnls["e_bold"]; ?>" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_BOLD_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/bold.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div unselectable='on' class="tbButton" ID="DECMD_ITALIC" TITLE="<?php echo $ARnls["e_italic"]; ?>" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_ITALIC_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/italic.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div unselectable='on' class="tbButton" ID="DECMD_UNDERLINE" TITLE="<?php echo $ARnls["e_underline"]; ?>" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_UNDERLINE_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/under.gif" WIDTH="23" HEIGHT="22">
  </div>
  
  <div unselectable='on' class="tbSeparator"></div>

  <div unselectable='on' class="tbButton" ID="DECMD_JUSTIFYLEFT" TITLE="<?php echo $ARnls["e_align_left"]; ?>" TBTYPE="toggle" NAME="Justify" LANGUAGE="javascript" onclick="return DECMD_JUSTIFYLEFT_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/left.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div unselectable='on' class="tbButton" ID="DECMD_JUSTIFYCENTER" TITLE="<?php echo $ARnls["e_align_center"]; ?>" TBTYPE="toggle" NAME="Justify" LANGUAGE="javascript" onclick="return DECMD_JUSTIFYCENTER_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/center.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div unselectable='on' class="tbButton" ID="DECMD_JUSTIFYRIGHT" TITLE="<?php echo $ARnls["e_align_right"]; ?>" TBTYPE="toggle" NAME="Justify" LANGUAGE="javascript" onclick="return DECMD_JUSTIFYRIGHT_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/right.gif" WIDTH="23" HEIGHT="22">
  </div>

  <div unselectable='on' class="tbSeparator"></div>

  <div unselectable='on' class="tbButton" ID="DECMD_ORDERLIST" TITLE="<?php echo $ARnls["e_numbered_list"]; ?>" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_ORDERLIST_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/numlist.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div unselectable='on' class="tbButton" ID="DECMD_UNORDERLIST" TITLE="<?php echo $ARnls["e_bulleted_list"]; ?>" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_UNORDERLIST_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/bullist.gif" WIDTH="23" HEIGHT="22">
  </div>
  
  <div unselectable='on' class="tbSeparator"></div>

  <div unselectable='on' class="tbButton" ID="DECMD_OUTDENT" TITLE="<?php echo $ARnls["e_decrease_indent"]; ?>" LANGUAGE="javascript" onclick="return DECMD_OUTDENT_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/deindent.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div unselectable='on' class="tbButton" ID="DECMD_INDENT" TITLE="<?php echo $ARnls["e_increase_indent"]; ?>" LANGUAGE="javascript" onclick="return DECMD_INDENT_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/inindent.gif" WIDTH="23" HEIGHT="22">
  </div>

  <div unselectable='on' class="tbSeparator"></div>

  <div unselectable='on' class="tbButton" ID="DECMD_HYPERLINK" TITLE="<?php echo $ARnls["e_link"]; ?>" LANGUAGE="javascript" onclick="return DECMD_HYPERLINK_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/link.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div unselectable='on' class="tbButton" ID="DECMD_IMAGE" TITLE="<?php echo $ARnls["e_insert_image"]; ?>" LANGUAGE="javascript" onclick="return DECMD_IMAGE_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/image.gif" WIDTH="23" HEIGHT="22">
  </div>
</div>


<div unselectable='on' class="tbToolbar" ID="AbsolutePositioningToolbar" TBSTATE="hidden">
  <div unselectable='on' class="tbButton" ID="DECMD_VISIBLEBORDERS" TITLE="<?php echo $ARnls["e_visible_borders"]; ?>" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_VISIBLEBORDERS_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/borders.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div unselectable='on' class="tbButton" ID="DECMD_SHOWDETAILS" TITLE="<?php echo $ARnls["e_show_details"]; ?>" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_SHOWDETAILS_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/details.gif" WIDTH="23" HEIGHT="22">
  </div>
  
  <div unselectable='on' class="tbSeparator"></div>

  <div unselectable='on' class="tbButton" ID="DECMD_MAKE_ABSOLUTE" TBTYPE="toggle" LANGUAGE="javascript" TITLE="<?php echo $ARnls["e_make_absolute"]; ?>" onclick="return DECMD_MAKE_ABSOLUTE_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/abspos.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div unselectable='on' class="tbButton" ID="DECMD_LOCK_ELEMENT" TBTYPE="toggle" LANGUAGE="javascript" TITLE="<?php echo $ARnls["e_lock"]; ?>" onclick="return DECMD_LOCK_ELEMENT_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/lock.gif" WIDTH="23" HEIGHT="22">
  </div>
  
  <div unselectable='on' class="tbSeparator"></div>
  
  <div unselectable='on' class="tbMenu" ID="ZORDER" LANGUAGE="javascript" tbOnMenuShow="return OnMenuShow(QueryStatusZOrderMenu, ZORDER)">
  <?php echo $ARnls["e_z_order"]; ?>
    <div unselectable='on' class="tbMenuItem" ID="ZORDER_BRINGFRONT" LANGUAGE="javascript" onclick="return ZORDER_BRINGFRONT_onclick()">
      <?php echo $ARnls["e_bring_to_front"]; ?>
    </div>
    <div unselectable='on' class="tbMenuItem" ID="ZORDER_SENDBACK" LANGUAGE="javascript" onclick="return ZORDER_SENDBACK_onclick()">
      <?php echo $ARnls["e_send_to_back"]; ?>
    </div>
 
    <div unselectable='on' class="tbSeparator"></div>

    <div unselectable='on' class="tbMenuItem" ID="ZORDER_BRINGFORWARD" LANGUAGE="javascript" onclick="return ZORDER_BRINGFORWARD_onclick()">
      <?php echo $ARnls["e_bring_forward"]; ?>
    </div>
    <div unselectable='on' class="tbMenuItem" ID="ZORDER_SENDBACKWARD" LANGUAGE="javascript" onclick="return ZORDER_SENDBACKWARD_onclick()">
      <?php echo $ARnls["e_send_backward"]; ?>
    </div>
 
    <div unselectable='on' class="tbSeparator"></div>

    <div unselectable='on' class="tbMenuItem" ID="ZORDER_BELOWTEXT" LANGUAGE="javascript" onclick="return ZORDER_BELOWTEXT_onclick()">
      <?php echo $ARnls["e_send_below_text"]; ?>
    </div>
    <div unselectable='on' class="tbMenuItem" ID="ZORDER_ABOVETEXT" LANGUAGE="javascript" onclick="return ZORDER_ABOVETEXT_onclick()">
      <?php echo $ARnls["e_bring_above_text"]; ?>
    </div>
  </div>
  
  <div unselectable='on' class="tbSeparator"></div>
  
  <div unselectable='on' class="tbButton" ID="DECMD_SNAPTOGRID" TITLE="<?php echo $ARnls["e_snap_to_grid"]; ?>" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_SNAPTOGRID_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/snapgrid.gif" WIDTH="23" HEIGHT="22">
  </div>
</div>

<div unselectable='on' class="tbToolbar" ID="TableToolbar" TBSTATE="hidden">
  <div unselectable='on' class="tbButton" ID="DECMD_INSERTTABLE" TITLE="<?php echo $ARnls["e_insert_table"]; ?>" LANGUAGE="javascript" onclick="return TABLE_INSERTTABLE_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/instable.gif" WIDTH="23" HEIGHT="22">
  </div>

  <div unselectable='on' class="tbSeparator"></div>

  <div unselectable='on' class="tbButton" ID="DECMD_INSERTROW" TITLE="<?php echo $ARnls["e_insertrow"]; ?>" LANGUAGE="javascript" onclick="return TABLE_INSERTROW_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/insrow.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div unselectable='on' class="tbButton" ID="DECMD_DELETEROWS" TITLE="<?php echo $ARnls["e_deleterows"]; ?>" LANGUAGE="javascript" onclick="return TABLE_DELETEROW_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/delrow.gif" WIDTH="23" HEIGHT="22">
  </div>
 
  <div unselectable='on' class="tbSeparator"></div>

  <div unselectable='on' class="tbButton" ID="DECMD_INSERTCOL" TITLE="<?php echo $ARnls["e_insertcol"]; ?>" LANGUAGE="javascript" onclick="return TABLE_INSERTCOL_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/inscol.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div unselectable='on' class="tbButton" ID="DECMD_DELETECOLS" TITLE="<?php echo $ARnls["e_deletecols"]; ?>" LANGUAGE="javascript" onclick="return TABLE_DELETECOL_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/delcol.gif" WIDTH="23" HEIGHT="22">
  </div>
  
  <div unselectable='on' class="tbSeparator"></div>

  <div unselectable='on' class="tbButton" ID="DECMD_INSERTCELL" TITLE="<?php echo $ARnls["e_insertcell"]; ?>" LANGUAGE="javascript" onclick="return TABLE_INSERTCELL_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/inscell.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div unselectable='on' class="tbButton" ID="DECMD_DELETECELLS" TITLE="<?php echo $ARnls["e_deletecells"]; ?>" LANGUAGE="javascript" onclick="return TABLE_DELETECELL_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/delcell.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div unselectable='on' class="tbButton" ID="DECMD_MERGECELLS" TITLE="<?php echo $ARnls["e_mergecells"]; ?>" LANGUAGE="javascript" onclick="return TABLE_MERGECELL_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/mrgcell.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div unselectable='on' class="tbButton" ID="DECMD_SPLITCELL" TITLE="<?php echo $ARnls["e_splitcell"]; ?>" LANGUAGE="javascript" onclick="return TABLE_SPLITCELL_onclick()">
    <img unselectable='on' class="tbIcon" src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/spltcell.gif" WIDTH="23" HEIGHT="22">
  </div>
</div>

<div id="dhtmled">
</div>
<!-- DEInsertTableParam Object -->
<object ID="ObjTableInfo" CLASSID="clsid:47B0DFC7-B7A3-11D1-ADC5-006008A5848C" VIEWASTEXT width="1" height="1" style="display: none;">
</object>

<!-- DEGetBlockFmtNamesParam Object -->
<object ID="ObjBlockFormatInfo" CLASSID="clsid:8D91090E-B955-11D1-ADC5-006008A5848C" VIEWASTEXT width="1" height="1" style="display: none;">
</object>

<script LANGUAGE="Javascript">
	CreateControl( "dhtmled",
                    "clsid:2D360201-FFF5-11D1-8D03-00A0C959BC0A",
                    "tbContentElement", "tbContentElement", { 'Scrollbars' : true, 'SourceCodePreservation' : 1 });
</script>

<SCRIPT LANGUAGE=JavaScript FOR=tbContentElement EVENT=onkeypress>
    myevent=tbContentElement.DOM.parentWindow.event;
    if (!wgCompose_keypress(myevent)) {
      tbContentElement.DOM.parentWindow.event.cancelBubble=true; 
	  tbContentElement.DOM.parentWindow.event.returnValue=false; 
    }
</SCRIPT>

<script LANGUAGE="javascript" FOR="tbContentElement" EVENT="onkeydown">
    myevent=tbContentElement.DOM.parentWindow.event;
    if (!wgCompose_check(myevent)) {
      tbContentElement.DOM.parentWindow.event.cancelBubble=true; 
	  tbContentElement.DOM.parentWindow.event.returnValue=false; 
    }
</script>

<script LANGUAGE="javascript" FOR="tbContentElement" EVENT="DisplayChanged">
<!--
return tbContentElement_DisplayChanged()
//-->
</script>

<script LANGUAGE="javascript" FOR="tbContentElement" EVENT="ShowContextMenu">
<!--
return tbContentElement_ShowContextMenu()
//-->
</script>

<script LANGUAGE="javascript" FOR="tbContentElement" EVENT="ContextMenuAction(itemIndex)">
<!--
return tbContentElement_ContextMenuAction(itemIndex)
//-->
</script>

<SCRIPT LANGUAGE=javascript FOR=tbContentElement EVENT=DocumentComplete>
<!--
 tbContentElement_DocumentComplete()
//-->
</SCRIPT>
<!-- Toolbar Code File. Note: This must always be the last thing on the page -->

<script LANGUAGE="Javascript">
  tbScriptletDefinitionFile = "<?php echo $AR->dir->www; ?>widgets/htmledit/ie/Toolbars/menubody.htm";
</script>
<script LANGUAGE="Javascript" SRC="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/Toolbars/tbmenus.js">
</script>

<script LANGUAGE="Javascript" SRC="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/Toolbars/toolbars.js">
</script>

</body>
</html>