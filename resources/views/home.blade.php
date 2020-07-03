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
			</div>
			<div class="flex-item"> 
				<br>
			</div>
			<div class="flex-item">
				<div style="box-shadow: 3px 3px #888888;" id="table"></div>
			</div>
			
			<div class="flex-item">
				<small> Last Updated  <a id="update" href="#">Click to update</a> </small>
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
	{title:"Shipment", field:"name", sorter:"string", align:"left"},
	{title:"Progress", field:"name", align:"center",width:300,visible:true,formatter:
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
	{title:"Time Consumed", field:"progress", sorter:"number", align:"left",visible:true,
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
		
	});
	
	</script>
</html>
