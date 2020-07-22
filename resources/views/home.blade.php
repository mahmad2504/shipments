<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Local Shipments Pakistan</title>
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
			<div style="font-weight:bold;font-size:20px;line-height: 50px;height:50px;background-color:#4682B4;color:white;" class="flex-item"> 
			 <img style="float:left;" height="50px" src="/images/mentor.png"></img>
			 <div style="margin-right:150px;">{{ $team }} - Local Shipments Dashboard - Pakistan</div>
			</div>
			<div class="flex-item"> 
			<small class="flex-item" style="font-size:12px;">This Dashboard lists down Open,In progress and recently delivered shipment tickets<a id="update" href="#"></a></small>
			</div>
			<hr>
			<div style="display:none" id="selectdiv">
				<span style="font-weight:bold;">Team&nbsp&nbsp</span><select  id='select'>Team</select><span>&nbsp&nbsp</span><span id="teamurl"></span>
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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/core.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/md5.js"></script>
	<script src="{{ asset('tabulator/js/tabulator.min.js') }}" ></script>
	<script src="{{ asset('attention/attention.js') }}" ></script>
	<script>
	//define data
	var labels = {};
	var admin = {{$admin}};
	
	var tabledata = @json($tickets);
	for(i=0;i<tabledata.length;i++)
	{
		var row=tabledata[i];
		row.label = row.label.replace(/ /g, '');
		if(row.label.length == 0)
			row.label = 'Others';
		if(row.label=='Others')
			continue;
		labels[row.label]=row.label;
	}
	labels['Others']='Others';
	$('#select').append('<option value="'+'Select'+'" selected="selected">'+'Select'+'</option>');
	for(var i in labels)
    {
		console.log(labels[i]);
		$('#select').append('<option value="'+labels[i]+'" >'+labels[i]+'</option>');
	}
	
	var columns=[
	{title:"Requestor", field:"requestor", sorter:"string", align:"left",width:"100"},
	{title:"Hardware Details", field:"details", sorter:"string", align:"left",width:"350",formatter:
		function(cell, formatterParams, onRendered)
		{
			var row = cell.getRow().getData();
			var url = row.url;
			if(admin==1)
				return "<a href='"+url+"'>"+cell.getValue()+'</a>';
			else
				return cell.getValue();
			//return '<a href="'+jira_url+cell.getValue()+'">'+cell.getValue()+'</a>';
		}
	},
	{title:"Source", field:"source", sorter:"string", align:"left",width:"120"},
	{title:"Destination", field:"dest", sorter:"string", align:"left",width:"120"},
	{title:"Priority", field:"priority", sorter:"string", align:"left",width:"80"},
	{title:"Team", field:"label", sorter:"string", align:"left",width:"80"},
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
			var time_consumed = cell.getValue();
			var row = cell.getRow();
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
	{title:"Delivery", field:"due", sorter:"string", align:"left",visible:true,
		formatter:function(cell, formatterParams, onRendered)
		{
			var data = cell.getRow().getData();
			if((data.dueComplete === true)&&(data.progress ==100))
			{
				$(cell.getRow().getElement()).css({"color":"#989898"});
				return new Date(data.deliveredon).toString().substr(0,15);
			}
			return new Date(cell.getValue()).toString().substr(0,15);
		}
	},
	{title:"Due", field:"deliveredon", sorter:"string", align:"left",visible:false},
	{title:"Due", field:"dueComplete", sorter:"string", align:"left",visible:false}

	];
	$(document).ready(function()
	{
		var getUrl = window.location;
		var baseUrl = getUrl .protocol + "//" + getUrl.host;
		console.log(baseUrl);
		var table = new Tabulator("#table", {
			data:tabledata,
			columns:columns,
			tooltips:true,
			//autoColumns:true,
			initialSort:[
				{column:"due", dir:"dsc"}, //sort by this first
			]
		});
		$('select').on('change', function() {
			if(this.value == "Select")
				table.clearFilter(true);
			else
			{
				table.setFilter("label", "=", this.value);
				var md5=CryptoJS.MD5(this.value.toLowerCase()).toString();
				var url  = baseUrl + "/" + this.value.toLowerCase()+ "/" + md5.substring(0,6);
				
				$('#teamurl').html('<a href="'+url+'">Share Link</a>');
			}
		});
		if(admin==1)
			$('#selectdiv').show();
		//table.setFilter("label", "=", "AND");
		
	});
	
	</script>
</html>
