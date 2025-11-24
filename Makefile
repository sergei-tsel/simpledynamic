start:
	php -S localhost:8000 app/View/Views/index.php
migrate:
	php app/Model/ORM/Models/run_migrations.php
rollback:
	php app/Model/ORM/Models/rollback_all_migrations.php
