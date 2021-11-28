<?php
/*
 * functions.php
 */

/**
 * @param mixed $dump
 * @param bool $die
 * @param bool $all
 *
 * @return mixed
 */
function dump($dump, bool $die = false, bool $all = false) 
{
	global $USER;
	if ($USER->IsAdmin() || $all === true)
		echo '<pre>',var_dump($dump),'</pre>';

	if ($die === true) die();
}
?>