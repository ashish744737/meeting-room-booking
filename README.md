# meeting-room-booking
meeting room booking app

Steps : 
1. use the command : composer install
2. rename .env.example to .env 
3. For detabase setup please follow below setup in .env

------ for mysql database ---------
<br>
DB_CONNECTION=mysql<br>
DB_HOST=127.0.0.1<br>
DB_PORT=3306<br>
DB_DATABASE=database_name<br>
DB_USERNAME=root<br>
DB_PASSWORD=<br>
<hr>

4. use "php artisan migrate" command to migrate database
5. use "php artisan db:seed" command to seed the user data.
6. to run the program use the command : "php artisan serve"
7. to run the program use the command : "npm run dev"
8. register a new user and login with that one

<hr>
