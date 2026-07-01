//placeholder for PHP_FPM document root

//PHP-FPM needs a physical document root to exist when it starts. 
//This file gives it something to find. 
//You'll never call this endpoint — all real logic is under api/.
<?php
echo "PHP container is running. API lives at /api/.";
