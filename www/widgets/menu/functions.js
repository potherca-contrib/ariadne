// default values for opening windows
	windowprops=new Array();
	windowprops['common']='resizable';
	windowprops['full']='directories,location,menubar,status,toolbar,resizable,scrollbars';
	windowprops['object_fs']=windowprops['common']+',height=100,width=400';
	windowprops['object_new']=windowprops['common']+',height=275,width=450';
	windowprops['edit_find']=windowprops['common']+',height=400,width=500';
	windowprops['edit_preferences']=windowprops['common']+',height=400,width=500';
	windowprops['edit_object_data']=windowprops['common']+',height=275,width=450';
	windowprops['edit_object_cache']=windowprops['common']+',height=250,width=250';
	windowprops['edit_object_layout']=windowprops['common']+',height=400,width=700';
	windowprops['edit_object_shortcut']=windowprops['common']+',height=250,width=450';
	windowprops['edit_object_grants']=windowprops['common']+',height=300,width=550';
	windowprops['edit_object_types']=windowprops['common']+',height=150,width=250';
	windowprops['edit_object_nls']=windowprops['common']+',height=250,width=400';
	windowprops['edit_priority']=windowprops['common']+',height=150,width=250';
	windowprops['view_fonts']=windowprops['common']+',height=300,width=450';
	windowprops['help']=windowprops['common']+',height=350,width=450';
	windowprops['help_about']=windowprops['common']+',height=200,width=450';
	windowprops['_new']=windowprops['full'];

	function viewpath(path) {
		test=new String(path);
		if (test.substr(test.length-1)!='/') {
			test+='/';
		}
		re=/\/+/g
		test=test.replace(re,'/');
		top.View(test);
		return false;
	}

	function arshow(windowname, link) {
		properties=windowprops[windowname];

		/* FIXME: doesn't work without frames on mozilla
		windowsize=top.Get(windowname);
		if (windowsize) {
			alert('windowsize='+windowsize);
			properties=properties+','+windowsize;
		}
		*/
		workwindow=window.open(link, windowname, properties);
		workwindow.focus();
	}

	function artoggleexplorerbar() {
		if (document.all) {
			icon=document.all['explorerbar_icon'];
			treestatus=top.toggletree();
			if (treestatus=='hidden') {
				icon.className='unselectedOption';
			} else {
				icon.className='selectedOption';
			}
		}
	}

	function setViewit(type) {
		alert(type);
	}

	function editobject() {
		newwindow=window.open('edit.object.data.phtml', 'newwindow', windowproperties);
		newwindow.focus();
		return false;
	}