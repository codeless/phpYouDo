<div class="row">
	<div class="large-12 columns">
		<table>
			<thead>
				<tr>
				<?php

				$headings = array();
				foreach ($data[0] as $name => $value) {
					$headings[] = $name;
				}
				echo '<th>',implode(
					'</th><th>',
					$headings),'</th>',PHP_EOL;

				?>
				</tr>
			</thead>
			<tbody>
				<?php

				foreach ($data as $row) {
					echo '<tr><td>',implode(
						'</td><td>',
						(array) $row),
						'</td></tr>',PHP_EOL;
				}

				?>
			</tbody>
		</table>
	</div>
</div>
