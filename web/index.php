<!DOCTYPE html>
<html>
	<head>
            <script type='text/javascript' src='NumbersMenu.js'></script>
            <script type='text/javascript' src='Cells.js'></script>
            <script type='text/javascript' src='Numbers.js'></script>
            <script type='text/javascript' src='HorizontalNumbers.js'></script>
            <script type='text/javascript' src='VerticalNumbers.js'></script>
            <script type='text/javascript' src='jquery.js'></script>
            <script type='text/javascript' src='Field.js'></script>
            <link rel="stylesheet" type="text/css" href="style.css" />
	</head>
	<body>
	<a href='../'>назад</a><br>
	<button onclick="deleteNumbers()">Удалить числа</button>
	<br>
	<button onclick="solve()">Разгадать</button>
	
	<div id='field'></div>
	
	<script type="text/javascript">
		function set_cookie ( name, value, exp_y, exp_m, exp_d, path, domain, secure )
		{
		  var cookie_string = name + "=" + escape ( value );
		 
		  if ( exp_y )
		  {
			var expires = new Date ( exp_y, exp_m, exp_d );
			cookie_string += "; expires=" + expires.toGMTString();
		  }
		 
		  if ( path )
				cookie_string += "; path=" + escape ( path );
		 
		  if ( domain )
				cookie_string += "; domain=" + escape ( domain );
		  
		  if ( secure )
				cookie_string += "; secure";
		  
		  document.cookie = cookie_string;
		}
		
		function get_cookie ( cookie_name )
		{
		  var results = document.cookie.match ( '(^|;) ?' + cookie_name + '=([^;]*)(;|$)' );
		 
		  if ( results )
			return ( unescape ( results[2] ) );
		  else
			return null;
		}
	
		function delete_cookie ( cookie_name )
		{
		  var cookie_date = new Date ( );  // Текущая дата и время
		  cookie_date.setTime ( cookie_date.getTime() - 1 );
		  document.cookie = cookie_name += "=; expires=" + cookie_date.toGMTString();
		}
	
		//console.log(get_cookie('horNums'));
		//console.log(get_cookie('vertNums'));
		
		
		
		/*
		hor = [[1,2],[2,4],[4,2,2],[2,1,1,3],[1,3,2,4],[2,7,4,2,2],[1,2,5,2,3],[1,1,3,2,2],[2,3,1,2],[2,4,2,1],[1,1,5,3],[2,2,3,1,4],[1,7,3,7],[18,4],[3,4,2,5,3],[1,5,1,5,7],[1,4,2,4,11,14],[1,2,1,2,1,3,2,23],[2,1,2,3,1,22],[3,3,3,1,2,2,1,11,7],[2,1,2,4,3,1,1,2,7,7],[2,6,5,2,2,2,4,3,8],[2,6,8,1,2,4,8],[2,4,7,2,3,6,2,5],[2,2,7,2,2,6,5,4],[3,3,6,3,3,7,8,4],[13,3,11,11,3],[4,4,2,11,10,3],[5,4,11,9,3],[1,7,10,9,3],[3,5,8,10,4],[8,3,9,11,4,8],[8,3,11,13,4,8],[4,3,13,2,16,4],[4,2,5,13,21,6,3,3],[4,2,3,14,21,6,3,3],[4,3,13,21,7,3,3],[4,3,12,22,7,3,3],[4,3,11,22,7,3,3],[1,3,1,11,23,7,1],[2,2,3,1,3,5,10,12,8,6],[2,3,3,2,2,5,8,7,6,7,6],[2,4,5,2,2,5,6,9,2,4,2,6],[2,4,5,2,2,8,9,3,3,2,2,6],[2,5,1,4,3,3,3,2,9,3,2,2,6],[1,1,8,2,3,5,9,4,2,1,1],[8,1,13,4,6,9,4,3,2,4],[8,2,4,5,5,2,3,9,3,4,2,4],[8,2,1,2,2,4,4,2,4,9,3,4,2,3],[8,1,1,2,4,3,3,3,5,8,4,7,2],[9,1,1,2,6,4,3,3,7,7,5,7,2],[7,1,1,1,2,6,3,2,3,8,5,6,7,2],[1,2,4,7,3,2,1,2,5,2,1,6,1],[5,3,2,4,7,3,2,8,4,4,3,4,5,2],[5,4,1,4,7,3,2,8,3,4,4,5,2,3,2],[5,4,1,4,7,3,2,8,3,5,4,5,1,2,2],[5,4,1,3,7,2,2,11,5,4,6,2,2,2],[10,5,8,2,2,10,6,11,2,2,2],[10,5,7,1,3,9,7,11,1,2,2],[6,1,3,2,4,2,1],[6,2,3,2,1,3,2,2],[5,2,3,8,1,2],[5,2,3,6,1,2],[6,2,3,1,3,2],[7,2,4,1,5],[9,2,4,1,1,3],[2,4,2,3,6,2,2,2],[1,1,2,2,2,11,9],[6,1,4,2,12,3,2],[6,7],[1,1,2,4,28,5],[7,7,1,4,34],[17,3,2,1,32],[22,3,2,2,20,4],[22,1,6,14]];
		vert = [[8,4,2,5,5,6,6,4],[4,2,1,2,5,2,5,5,6,6,4],[3,2,3,9,2,5,6,6,4],[3,3,8,5,2,5,5,6,6,4],[4,4,3,3,3,2,6,6,6,4],[2,6,1,3,3,7,4,6,2,4],[1,1,5,1,6,1,10,3,6,6,3,4],[2,7,1,6,6,5,7,6,2,2,3],[12,4,2,1,1,2,5,9,1,3,3],[9,2,3,1,1,2,9,5,2,2,3],[2,2,2,2,4,1,2,3,7,3,4,3],[2,6,10,1,2,3,15,2,3],[1,2,3,9,2,2,1,3,10,1,4],[2,6,8,5,2,26,4],[2,4,8,6,2,4,16,4],[2,3,5,5,2,21,4],[2,2,3,3,5,13,2,4],[2,4,4,4,4,3,1,1,2],[5,3,3,6,2,3,11,1,2],[6,4,3,3,3,2,11,2],[6,4,2,4,5,2,10,2],[5,6,3,5,9,2,10,2],[8,22,2,9],[4,28,9,3],[2,4,15,5,7,4],[3,15,5,2,3],[3,19,10,2,1],[2,22,21,3],[1,24,6,15],[2,24,2,4,1],[2,9,12,5,9,3,2],[2,6,7,32],[2,3,26],[4,9,2,1],[5,11,10,1],[6,11,7,6,1],[6,10,7,6,2],[5,9,2,6,1,2],[5,10,8,6,1,2],[5,10,8,6,2,2],[4,12,6,6,2,2],[4,13,3,4,6,5],[4,15,5,3,3,5],[4,15,7,2,5,2,5],[3,16,8,2,4,4,1,5],[3,25,5,2,2,1,5],[3,26,2,2,4,1,5],[3,27,5,2,1,4],[3,39,1,4],[3,38,2,4],[3,3,10,17,2,4],[5,2,10,1,12,2,4],[5,1,9,3,5,2,4],[6,9,4,2,5,2,4],[5,8,4,7,3,4],[4,5,14,3,4],[4,4,6,6,2,3],[5,3,4,2,2,3],[5,5,4,3,6,2,3],[6,9,3,2,6,2,3],[21,3,8,3],[19,3,6,3],[15,5,5,3],[12,5,3,2,3],[11,6,4,3],[2,18,2,2,2],[2,5,8,8,4,2],[2,11,6,2,2,2,2],[2,5,8,9,1,2,3],[2,5,3,9,2,2,4],[2,5,5,7,8,4],[2,5,5,6,6,5,4],[2,5,5,6,4]];
		field = new Field('field',hor,vert);
		*/
		
		
		field = new Field('field',JSON.parse(get_cookie('horNums')),JSON.parse(get_cookie('vertNums')));
		field.draw();
		
		setInterval(function(){
			set_cookie('horNums',JSON.stringify(field.horNumsList));
			set_cookie('vertNums',JSON.stringify(field.vertNumsList));
		},5000);
		
		function solve()
		{
                    $.ajax({
                        url: 'solve.php',
                        dataType: 'json',
                        method: 'POST',
                        //async: true,
                        data: {horNums:field.horNumsList,vertNums:field.vertNumsList,cells:field.cellsList},
                        success: function (data) {
                            field.redraw(data['cells']);
                            console.log(data);
                        },
                        //complete: function(){
                        //    console.log('ssf');
                        //}
                    });
                    //console.log(field.horNumsSum+' '+field.vertNumsSum);
		}
		
		function deleteNumbers()
		{
			delete_cookie('horNums');
			delete_cookie('vertNums');
		}
		
	</script>

	</body>
</html>