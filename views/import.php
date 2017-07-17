<div class="import-wrapper">
	<form action="" method="POST" accept-charset="UTF-8" enctype="multipart/form-data">
        <input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>" />
        <input type="hidden" name="tab" value="<?php echo esc_attr( $_GET['tab'] ); ?>" />
        <input type="hidden" name="table" value="<?php echo esc_attr( $_GET['table'] ); ?>" />

		<div class="form-control">
			<input type="file" name="file_csv" id="file_csv"><br>
			<button type="submit">Upload</button>
		</div>
	</form>
</div>