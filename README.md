# intra-payroll# payroll-hris-system
"# payroll-hris-system" 

--Localhost setup--
if using php version lower than 8.1 add --ignore-platform-reqs
1. composer install --ignore-platform-reqs
2. create .env file
3. create server.php just copy the code from Xserver.php file
4. php artisan config:cache
5. import database hris_prl_db from database folder
6. composer dump-autoload
7. php artisan config:clear
8. php artisan serve


