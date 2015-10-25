<?php

namespace Code42\Root\Controller;

class UserController {
	public function sayHello($name) {
		echo "<b style='color: green;'>Hello, $name!</b>";
	}
}