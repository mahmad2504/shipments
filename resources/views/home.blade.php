<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>
		<link rel="stylesheet" href="{{ asset('tabulator/css/tabulator.min.css') }}" />
		<link rel="stylesheet" href="{{ asset('attention/attention.css') }}" />
    <style>
		.tabulator [tabulator-field="summary"]{
				max-width:200px;
		}

		.flex-container {
			height: 100%;
			padding: 0;
			margin: 0;
			display: -webkit-box;
			display: -moz-box;
			display: -ms-flexbox;
			display: -webkit-flex;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.row {
			width: auto;
			
		}
		.flex-item {
			text-align: center;
		}

    </style>
    </head>
    <body>
	<div class="flex-container">
		<div class="row"> 
			<div class="flex-item"> 
				<h4>Local Shipments Dashboard - Pakistan</h4>
			</div>
			<div class="flex-item"> 
				<br>
			</div>
			<div class="flex-item">
				<div style="box-shadow: 3px 3px #888888;" id="table"></div>
			</div>
			
			<div class="flex-item">
				<small style="font-size:10px;">Dashboard created by mumtaz.ahmad@siemens.com for engineering operations Pakistan<a id="update" href="#"></a></small><br>
				<small style="font-size:10px;">Last updated on {{$lastupdated}} PKT</small>
			</div>
		</div>
	</div>
    </body>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
	<script type="text/javascript" src="https://oss.sheetjs.com/sheetjs/xlsx.full.min.js"></script>
	<script src="{{ asset('tabulator/js/tabulator.min.js') }}" ></script>
	<script src="{{ asset('attention/attention.js') }}" ></script>
	<script>
	//define data
	var tabledata = @json($tickets);
	console.log(tabledata);
	
	

	var columns=[
	
	{title:"Hardware Details", field:"details", sorter:"string", align:"left",width:"350"},
	{title:"Source", field:"source", sorter:"string", align:"left",width:"150"},
	{title:"Dest", field:"dest", sorter:"string", align:"left",width:"150"},
	{title:"Team", field:"label", sorter:"string", align:"left",width:"100"},
	{title:"Shipment", field:"name", sorter:"string", align:"left",visible:false},
	{title:"Status", field:"name", align:"center",width:300,visible:true,formatter:
		function(cell, formatterParams, onRendered)
		{
			var row = cell.getRow().getData();
			//row.checkItems;
			//row.checkItemsChecked;
			var imagename = "/images/"+row.checkItems+"_"+row.checkItemsChecked+".png";
			$(cell.getElement()).css({"background":"white"});
			$(cell.getElement()).css({"padding":"0px"});
			$(cell.getElement()).css({"margin":"0px"});
			$(cell.getElement()).css({"height":"23px"});
			return "<img  width='200px' src='"+imagename+"'>";
			//return '<a href="'+jira_url+cell.getValue()+'">'+cell.getValue()+'</a>';
		}
	},
	{title:"Progress", field:"progress", sorter:"number", align:"left",visible:false,
			formatter:function(cell, formatterParams, onRendered)
		{
			time_consumed = cell.getValue();
			$(cell.getElement()).css({"background":"white"});
			//if(time_consumed == 100)
			//{
			//	return  '<span style="text-align: center;display: inline-block;width:'+'100'+'%;color:white;background-color:grey;"><small>'+time_consumed+'%</small></span>';
			//}
			if(time_consumed <50)
			{
				bcolor='Orange';
				fcolor='white';
			}
			else if(time_consumed <75)
			{
				bcolor='DarkSeaGreen';
				fcolor='black';
			}
			else if(time_consumed <100)
			{
				bcolor='MediumSeaGreen';
				fcolor='white';
			}
			else
			{
				
				bcolor='green';
				fcolor='white';
				return  '<span style="text-align: center;display: inline-block;width:'+time_consumed+'%;color:'+fcolor+';background-color:'+bcolor+';"><small>Delivered</small></span>';

			}
			
			return  '<span style="text-align: center;display: inline-block;width:'+time_consumed+'%;color:'+fcolor+';background-color:'+bcolor+';"><small>'+time_consumed+'%</small></span>';
		}
	},
	{title:"Due", field:"due", sorter:"string", align:"left",visible:true,
		formatter:function(cell, formatterParams, onRendered)
		{
			return new Date(cell.getValue()).toString().substr(0,15);
		}
	}
	
	];
	$(document).ready(function()
	{
		var table = new Tabulator("#table", {
			data:tabledata,
			columns:columns,
			tooltips:true,
			//autoColumns:true,
		});
		//table.setFilter("label", "=", "AND");
		
	});
	
	</script>
</html>
