<style type="text/css">

#border {
border: 5px solid #1C6EA4;
border-radius: 9px;
}

table.blueTable {
  border: 1px solid #1C6EA4;

  width: 100%;
  text-align: left;
  border-collapse: collapse;
   table-layout:fixed;
	overflow: hidden;
}
table.blueTable td, table.blueTable th {
  border: 1px solid #AAAAAA;
  padding: 3px 2px;
}
table.blueTable tbody td {
  font-size: 13px;
}
table.blueTable tr:nth-child(even) {
  background: #D0E4F5;
}
table.blueTable thead {
  background: #1C6EA4;
  background: -moz-linear-gradient(top, #5592bb 0%, #327cad 66%, #1C6EA4 100%);
  background: -webkit-linear-gradient(top, #5592bb 0%, #327cad 66%, #1C6EA4 100%);
  background: linear-gradient(to bottom, #5592bb 0%, #327cad 66%, #1C6EA4 100%);
  border-bottom: 2px solid #444444;
}
table.blueTable thead th {
  font-size: 15px;
  font-weight: bold;
  color: #FFFFFF;
  border-left: 2px solid #D0E4F5;
}
table.blueTable thead th:first-child {
  border-left: none;
}

table.blueTable tfoot {
  font-size: 14px;
  font-weight: bold;
  color: #FFFFFF;
  background: #D0E4F5;
  background: -moz-linear-gradient(top, #dcebf7 0%, #d4e6f6 66%, #D0E4F5 100%);
  background: -webkit-linear-gradient(top, #dcebf7 0%, #d4e6f6 66%, #D0E4F5 100%);
  background: linear-gradient(to bottom, #dcebf7 0%, #d4e6f6 66%, #D0E4F5 100%);
  border-top: 2px solid #444444;
}
table.blueTable tfoot td {
  font-size: 14px;
}
table.blueTable tfoot .links {
  text-align: right;
}
table.blueTable tfoot .links a{
  display: inline-block;
  background: #1C6EA4;
  color: #FFFFFF;
  padding: 2px 8px;
  border-radius: 5px;
}

</style>

<?php

foreach ($packet as $element) {
    $unique_servers[$element['from']] = $element;
    $unique_servers[$element['to']]   = $element;
}

?>



<div id="border">
<table class="blueTable" width="100%">
	<tr>
		<td width="50%" valign="top">
			<table class="blueTable" width="100%">
				<tr>
					<td width="20%"><b>Date</b></td>
					<?php $i = 1;?>
					<?php foreach ($unique_servers as $key => $server): ?>
									<?php ${"server_" . $i} = $key;?>
									<?php $i++?>
									<td align="center"><b><?php echo $key; ?> </b></td>
								<?php endforeach?>
				</tr>

				<?php foreach ($packet as $key => $value): ?>
				<tr>
					<td width="20%" ><?php echo $value['date'] ?></td>

					<?php for ($i = 1; $i <= count($unique_servers); $i++): ?>


						<td align="center">
							<div onclick='selectMethod("<?php echo $value['id'] ?>","<?php echo count($packet) ?>")'>

									<?php if (preg_match('/' . $value['from'] . '/', ${'server_' . $i})): ?>
									 	<?php echo $value['method'] ?> &rarr; <?php echo $value['to'] ?>
									<?php endif;?>



							</div>
						</td>



					<?php endfor;?>
				</tr>
				<?php endforeach?>
			</table>
		</td>
		<td width="50%" valign="top">
			<?php for ($i = 0; $i < count($packet); $i++): ?>
				<div style='display: none;'id='method<?php echo $packet[$i]['id'] ?>'><?php echo preg_replace('/\n/', '<br>', $packet[$i]['head']) ?></div>
			<?php endfor;?>
		</td>
	</tr>

</table>
</div>



<script type="text/javascript">

	function selectMethod(id,total) {
		for (var i = 1; i <= total; i++) {
			document.getElementById("method"+i).style.display  = 'none';
		}
		document.getElementById("method"+id).style.display  = 'inline';
	}

	document.getElementById("method1").style.display  = 'inline';
</script>
