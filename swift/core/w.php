<form method="post" action="w.php">
	<input type="text" name="user_name" />
	<input type="checkbox" name="fav[]" value="0" />
	<input type="checkbox" name="fav[]" value="1" />
	<input type="range" min="10"  max="20" name="1.2" />
	<input type="submit"  value="Reg" />
</form>


<?php

if($_POST){
	var_dump($_POST);
}


					
					
					
		