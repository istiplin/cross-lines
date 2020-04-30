<?php
    include '../vendor/autoload.php';
    
    $dataName = 'fieldSolves.php';
    $datas = require '../tests/_data/'.$dataName;
    
    $fieldInd = 8;
    
    $data = $datas[$fieldInd];

    $field = new models\FastField($data[0], $data[1],$data[2],$data['name']);
	
?>
<!DOCTYPE html>
<html>
	<head>
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
<?php
    echo $field->name.'<br>';
    echo $field->sizeView().'<br>';
?>
	<button onclick="solve()">Разгадать</button>
	
	<div id='field'></div>
	
	<script type="text/javascript">
		field = new Field('field',<?=json_encode($field->horNums)?>,<?=json_encode($field->vertNums)?>);
		field.draw();

		function solve()
		{
			$.ajax({
				url: 'solve.php',
				dataType: 'json',
				method: 'POST',
				data: {horNums:field.horNumsList,vertNums:field.vertNumsList,cells:field.cellsList},
				success: function (data) {
					field.redraw(data['cells']);
					console.log('draw');
				}
			});
		}
	</script>

	</body>
</html>