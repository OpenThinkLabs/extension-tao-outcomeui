    /**
     * 
     * implements the grid display for the resultTable
     * Note that it relies on document attributes like document.dataUrl set up by the php _url in the tpl 
     * If the _url becomes available in javascript we shall change this.
     */
    /**
     * Initiate or refresh the '#result-table-grid grid with data from _url('data') and using the columns document.columns/document.models current selection of data
     */
    function showGrid() {
    $('#result-table-grid').jqGrid('GridUnload');
    var myGrid = $("#result-table-grid").jqGrid({
	url: document.dataUrl,
	postData: {'filter': document.JsonFilter, 'columns':document.columns},
	mtype: "post",
	datatype: "json",
	colNames: document.columns.values,
	colModel: document.models,
	rowNum:20,
	height:'auto',
	width: (parseInt($("#result-table-grid").width())),
	rowNum:20,
	rowList:[5,10,30],
	pager: '#pagera1',
	sortName: 'id',
	viewrecords: true,
	rownumbers: true,
	sortorder: "asc",
	gridview : true,
	caption: __("Delivery results"),
	onCellSelect: function(rowid,iCol,cellcontent, e) {helpers.openTab(__('Delivery Result'), document.getActionViewResultUrl+'?uri='+escape(rowid));}
    });
    jQuery("#result-table-grid").jqGrid('navGrid','#pagera1', {edit:false,add:false,del:false,search:false,refresh:false});
    jQuery("#result-table-grid").jqGrid('navButtonAdd',"#pagera1", {caption:"Column chooser",title:"Column chooser", buttonicon :'ui-icon-gear',onClickButton:function(){columnChooser();}});
    //jQuery("#result-table-grid").jqGrid('filterToolbar');
    }
    /**
     * Initiate the grid and shows it using a startup document.column and a document.models containing the ResultOfSubject property
     */
    function initiateGrid(){
    $.getJSON(document.getActionSubjectColumnUrl, document.JsonFilter, function (data) {setColumns(data.columns)});
    }
    
     /*
    * Triggers the jquery jqGrid functionnality allowing to select columns to be displayed
    */
    function columnChooser(){
     jQuery("#result-table-grid").jqGrid('columnChooser', {"title": "Select column"});
    }
    /**
     * Update document.columns/document.models current selection of data with columns
     */
    function setColumns(columns) {
	for (key in columns) {
		 //used for the inner function set in the formatter callback
		 var currentColumn = columns[key];
		 document.columns.push(columns[key]);
		 document.models.push({
			 'name': columns[key]['label'], 
			 cellattr: function(rowId, tv, rawObject, cm, rdata){return "data-uri=void, data-type=void";},
			 formatter : function(value, options, rData){return layoutData(value,currentColumn);
			 }
		 });

	 }
	 showGrid();
    }
    /**
     * Update document.columns/document.models current selection of data with columns
     */
    function removeColumns(columns) {
	   //loops on the columns parameter and find out if document.columns contains this column already. 
	   for (key in columns) {
		    for (dockey in document.columns) {
		    //if the property delivery result is the same and the facet (score, response) is the same) 
		    if ((document.columns[dockey].ca == columns[key].ca) & (document.columns[dockey].type == columns[key].type)){
			 document.columns.splice(dockey,1);
			 //document.models.splice(modelkey,1);
			}
		    }

		    for (modelkey in document.models) {
		    if ((document.models[modelkey].name == columns[key].label)){
			    document.models.splice(modelkey,1);
			}
		    }
	   }
	    showGrid();
    }
    
    /**
     * Bind the click event to the function function and toggle the buttons class between fromButton toButton
     * @param {type} buttonID
     */
    function bindTogglingButtons(fromButton, tobuttonID, actionCallUrl, operationType){
	
	    $(fromButton).click(function(e) {
	    e.preventDefault();
	    if ($(fromButton).hasClass("ui-state-default")) {
	    $(fromButton).addClass("ui-state-disabled").removeClass("ui-state-default");
	    $(tobuttonID).addClass("ui-state-default").removeClass("ui-state-disabled");
	    $.getJSON( actionCallUrl
		    , document.JsonFilterSelection
		    , function (data) {
			switch (operationType){
			case "remove":removeColumns(data.columns);break;
			case "add":setColumns(data.columns);break;
			default:removeColumns(data.columns);break;
			}
			
		    }
	    );
	    }
	    });
	
    }
    /**
    * Transforms plain data into html data based on the property or based on the type of data sent out by the server (loose)
    */
    function layoutData(data, column){
    //Simple Properties 
    if (column.type == "tao_models_classes_table_PropertyColumn"){
    switch (column.prop){
	case document.resultOfSubjectConstant: {return  "<span class=highlight>"+data+"</span>";}
	default:return data;
	}
    }
    //Grade properties
    else if ((column.type == "taoResults_models_classes_table_GradeColumn")){
    return  "<span class=numeric>"+data+"</span>";
    }
    //Actual responses properties
    else if ((column.type == "taoResults_models_classes_table_ResponseColumn")){
	return layoutResponse(data);
	}
    }